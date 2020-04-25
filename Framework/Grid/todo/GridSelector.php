<?php 

/*

todo: clear selection link
//'defaultOrderBy' => array(array((string)$ds->expressionInFactory(VademecumProduct::FIELD_ID,'(1,2,4)')=>'desc')),

 */

class StrixGridSelector {
	/**
	 * @var StrixGrid
	 */
	protected $grid;
	private $name;
	protected $idColName;
	protected $value;
	protected $cookieName;
	protected $headerColumnName;
	
	function __construct(StrixGrid $grid, $name, $idColName, $params=array()) {
		$this->grid = $grid;
		$this->idColName = $idColName;
		$this->setName($name);
		$this->cookieName='StrixCheckboxSelector_'.$name;
		
		$serializedValue=$_COOKIE[$this->cookieName];
		unset($_COOKIE[$this->cookieName]);
		$anonFunc='strixGridSelectorContentFunc'.uniqid();
		$this->value=self::splitSerializedValue($serializedValue);
		eval('function '.$anonFunc.'($val,$args,$grid) { return StrixGridSelector::contentFuncHelper(\''.$this->name.'\',$args[\''.$this->idColName.'\']); }');
		$th = $this->grid->getTableHeader();
		$checkboxColumnTitle='';
		$this->headerColumnName=$this->getName().'_'.'col';
		$th=array_merge(array($headerColumnName=>array(
			'title' => $checkboxColumnTitle,
			'noSort' => true,
			'noSearch' => true,
			'contentFunc' => array(
				'function' => $anonFunc,
				'args' => array($this->idColName)
			)
		)),$th);
		$this->grid->setTableHeader($th);
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public static function splitSerializedValue($serializedValue) {
		$data=array(
			'serializedValue'=>$serializedValue,
			'allSelected'=>(strpos($serializedValue,'ALL:') === 0),
			'selectedItems'=>array(),
			'unSelectedItems'=>array(),
		);
		if (strlen(trim($serializedValue))===0) {
			$data['allSelected']=false;
			return $data;
		}
		if (!$data['allSelected']) {
			if (!strpos($serializedValue,'NONE:') === 0) {
				throw new Exception('Bad serialized value');
			}
		}
		if ($data['allSelected']) {
			$serializedValue=substr($serializedValue,4);
		} else {
			$serializedValue=substr($serializedValue,5);
		}
//TODO: IMPLEMENT UNESCAPE
		if (strlen(trim($serializedValue))==0) {
			$items = array();
		} else {
			$items = explode(',',$serializedValue);
		}
		if ($data['allSelected']) {
			$data['unSelectedItems'] = $items;
		} else {
			$data['selectedItems'] = $items;
		}
		
		return $data;
	}
	
	/**
	 * @return StrixGridExpression
	 */
	public function getFilter() {
		$ds=$this->grid->getDataSource();
		if ($this->value['allSelected']) {
			$items=$this->value['unSelectedItems'];
		} else {
			$items=$this->value['selectedItems'];
		}
		if (!empty($items)) {
			$expression=$ds->expressionInFactory($this->idColName,$ds->valueArrayFactory($items));
		} else {
			$expression=$ds->expressionEqualToFactory($ds->valueBooleanFactory(true),$ds->valueBooleanFactory(false));
		}
		if ($this->value['allSelected']) {
			$expression=$ds->expressionNotFactory($expression);
		} 
		$currentFilter=$ds->getFilter();
		if ($currentFilter) {
			$expression = $ds->expressionAndFactory($currentFilter, $expression);
		}
		return $expression;
	}
	
	public static function contentFuncHelper($name, $val) {
		return "<input type=\"checkbox\" autocomplete=\"off\" name=\"".$name."\" value=\"".$val."\" disabled=\"disabled\" />";
	}
		
	function getField() {
		Strix5::include_file_once('strix-grid-selector.css','css');
		$ret = $this->getJavaScript();
		$ret .= '<div class="strix-grid-selector">
			<span class="strix-grid-selector-select-all"> 
			<input id="'.$this->getName().'-select-all" class="strix-grid-selector-select-all-checkbox" type="checkbox" disabled=\"disabled\" autocomplete="off" />
			<label for="'.$this->getName().'-select-all">Select all</label>
			</span>			
			<button id="'.$this->getName().'-invert" class="strix-grid-selector-invert">Invert selection</button>
			<button id="'.$this->getName().'-select-none" class="strix-grid-selector-select-none">Select none</button>
			<div id="StrixCheckboxSelectorInfo" style="display:none"></div>
			<script type="text/javascript">
			<!-- // begin
				StrixCheckboxSelector._addListener(window,\'load\',function(){var cs=new StrixCheckboxSelector(\''.$this->getName().'\');cs.selector.unserialize(\''.$this->value['serializedValue'].'\')});
			// end -->
			</script>
			</div>
		';
		return $ret;
	}
	
	static private $javascriptLoaded = false;
	private function getJavaScript() {
		if ($this->readOnly) return '';
		if (self::$javascriptLoaded) return '';
		ob_start();
		?>
		<script type="text/javascript">
		<!-- // begin

			StrixSelector=function(instanceName) {
				StrixSelector.instances[instanceName]=this;
				this.instanceName=instanceName;
				this._events=new Object();
				this._selectedItems=new Array();
				this._unSelectedItems=new Array();
				this.allSelected=false;
			}
	
			StrixSelector.prototype.attachEvent=function (type, e) {
				var eventArray=this._events[type];
				if (!eventArray) {
					eventArray=new Array();
					this._events[type]=eventArray;
				}
				eventArray[eventArray.length]=e;
			}
			StrixSelector.prototype.triggerEvent=function (type,param) {
				var eventArray=this._events[type];
				if (eventArray) {
					for (var i=0; i<eventArray.length; i++) {
						eventArray[i](this,param);
					}
				}
			}
			StrixSelector.prototype.itemSelectedIndex=function(item) {
				for (var i=0; i<this._selectedItems.length; i++) {
					if (this._selectedItems[i] == item) {
						return i;
					}
				}
				return false;
			}
			StrixSelector.prototype.itemUnSelectedIndex=function(item) {
				for (var i=0; i<this._unSelectedItems.length; i++) {
					if (this._unSelectedItems[i] == item) {
						return i;
					}
				}
				return false;
			}
			StrixSelector.prototype.select=function(item) {
				if (this.allSelected) {
					var unSelectedItemsIndex = this.itemUnSelectedIndex(item);
					if (unSelectedItemsIndex !== false) {
						do {
							this._unSelectedItems.splice(unSelectedItemsIndex,1);
						} while (unSelectedItemsIndex = this.itemUnSelectedIndex(item)!== false);
						this.triggerEvent('select',item);
						this.triggerEvent('change');
					}
				} else {
					var selectedItemsIndex = this.itemSelectedIndex(item);
					if (selectedItemsIndex === false) {
						this._selectedItems[this._selectedItems.length]=item;
						this.triggerEvent('select',item);
						this.triggerEvent('change');
					}
				}
			}
			StrixSelector.prototype.unselect=function(item) {
				if (this.allSelected) {
					var unSelectedItemsIndex = this.itemUnSelectedIndex(item);
					if (unSelectedItemsIndex === false) {
						this._unSelectedItems[this._unSelectedItems.length]=item;
						this.triggerEvent('unselect',item);
						this.triggerEvent('change');
					}
				} else {
					var selectedItemsIndex = this.itemSelectedIndex(item);
					if (selectedItemsIndex !== false) {
						do {
							this._selectedItems.splice(selectedItemsIndex,1);
						} while (selectedItemsIndex = this.itemSelectedIndex(item)!== false);
						this.triggerEvent('unselect',item);
						this.triggerEvent('change');
					}
				}
			}
			StrixSelector.prototype.count=function() {
				if (this.allSelected) {
					return 0 - this._unSelectedItems.length;;
				}
				return this._selectedItems.length;
			}
			StrixSelector.prototype.toggle=function(item){
				if (this.isSelected(item)) {
					this.unSelect(item);
				} else {
					this.select(item);
				}
			}
			StrixSelector.prototype.isSelected=function(item) {
				if (this.allSelected) {
					return (this.itemUnSelectedIndex(item) === false);
				} else {
					return (this.itemSelectedIndex(item) !== false);
				}
			}
			StrixSelector.prototype.unSelectAll=function() {
				this.allSelected=false;
				this._selectedItems=new Array();
				this._unSelectedItems=new Array();
				this.triggerEvent('unselectall');
				this.triggerEvent('change');
			}
			StrixSelector.prototype.selectAll=function() {
				this.allSelected=true;
				this._selectedItems=new Array();
				this._unSelectedItems=new Array();
				this.triggerEvent('selectall');
				this.triggerEvent('change');
			}
			StrixSelector.prototype.invertAll=function() {
				if (this.allSelected) {
					var unSelectedItems = this._unSelectedItems;
					this.unSelectAll();
					for (var i=0; i<unSelectedItems.length; i++) {
						this.select(unSelectedItems[i]);
					}
				} else {
					var selectedItems = this._selectedItems;
					this.selectAll();
					for (var i=0; i<selectedItems.length; i++) {
						this.unselect(selectedItems[i]);
					}
				}
			}
			StrixSelector.prototype.toString=function() {
				var info;
				info = 'instance name: ' + this.instanceName;
				info += '\n';
				info += 'selected count: ' + this.count(); 
				info += '\n';
				if (this.allSelected) {
					info += 'selected: all';
					if (this._unSelectedItems.length > 0) {
						info += ' except ' + this._unSelectedItems.join(', ');
					}
				} else {
					if (this.count() > 0) {
						info += 'items: ' + this._selectedItems.join(', ');
					} else {
						info += 'selected: none';
					}
				}
				return info; 
			}
			StrixSelector.prototype.serializeEscape=function(t) {
				t = t.toString();
				t=t.replace(/,/g,',,');
				return t;
			}
			StrixSelector.prototype.serializeUnescape=function(t) {
				t = t.toString();
				t=t.replace(/,,/g,',');
				return t;
			}
			StrixSelector.prototype.serialize=function() {
				var ret;
				if (this.allSelected) {
					var list='';
					for (var i=0; i<this._unSelectedItems.length; i++) {
						list += (list ? ',':'') + this.serializeEscape(this._unSelectedItems[i]);
					}
					ret='ALL:'+list;
				} else {
					var list='';
					for (var i=0; i<this._selectedItems.length; i++) {
						list += (list ? ',':'') + this.serializeEscape(this._selectedItems[i]);
					}
					ret='NONE:'+list;
				}
				return ret;
			}
			StrixSelector.prototype.unserialize=function(t) {
				if (t.toString().replace(/^\s+|\s+$/g,'').length==0) {
					this.unSelectAll();
					return;
				}
				var allSelected=(t.toString().indexOf('ALL:') == 0)
				if (!allSelected) {
					if (t.toString().indexOf('NONE:') != 0) {
						throw 'StrixSelector.unserialize bad string: '+t.toString();
					}
				} else {
					t.length == 0
				}
				if (allSelected) {
					t = t.substring(4);
				} else {
					t = t.substring(5);
				}
				t=t.replace(/^\s+|\s+$/g,"")
				var arr;
				if (t.length>0) {
					arr=t.split(/,/);
				} else {
					arr=new Array();
				}
//TODO: IMPLEMENT UNESCAPE
				if (allSelected) {
					this.selectAll();
					for (var i=0; i<arr.length; i++) {
						this.unselect(arr[i]);
					}
				} else {
					this.unSelectAll();
					for (var i=0; i<arr.length; i++) {
						this.select(arr[i]);
					}
				}
			}
			StrixSelector.instances=new Object();
			StrixSelector.instance=function (instanceName) {
				var ret=StrixSelector.instances[instanceName];
				if (!ret) {
					ret=new StrixSelector(instanceName);
				}
				return ret;
			}

		
			StrixCheckboxSelector=function(instanceName){
				this.instanceName=instanceName;
				this.selector=new StrixSelector(this.instanceName);
				this.selector.debug=function(t) {
					var infoDiv=document.getElementById('StrixCheckboxSelectorInfo');
					if (infoDiv) {
						infoDiv.innerHTML = t.toString().replace(/\n/g,'<br />')+'<br /><br />'+infoDiv.innerHTML;
					}	
				}
				this.attachEvents();
			}
			StrixCheckboxSelector._addListener=function(oNode, sEventType, fnCallback) {
				if(document.addEventListener) {
					oNode.addEventListener(sEventType, function(oEvent) {
						fnCallback(oEvent);
					}, false);
				} else if(document.attachEvent) {
					oNode.attachEvent("on" + sEventType, function() {
						fnCallback();
					});     
				}
			}
			StrixCheckboxSelector.getCookie = function (name) {
				name = String(name || '');
				if (!name) return '';
				var ck = String(self.document.cookie);
				var start = ck.indexOf(name + '=') || 0;
				if (start < 0) return null;
				var len = start + name.length + 1;
				if (name != ck.substring(start, start + name.length)) return null;
				var end = ck.indexOf(';', len);
				if (end < 0) end = ck.length;
				return unescape(ck.substring(len, end));
			}

			StrixCheckboxSelector.setCookie = function(name, value, expires, path, domain, secure) {
				name = String(name || '');
				if (!name) return;
				var p = new Array();
				p[p.length] = name + '=' + escape(String(value));
				if (expires) p[p.length] = 'expires=' + String(expires.toGMTString());
				if (path) p[p.length] = 'path=' + String(path);
				if (domain) p[p.length] = 'domain=' + String(domain);
				if (secure) p[p.length] = 'secure';
				p = p.join(';');
				if (p) self.document.cookie = p;
			}

			StrixCheckboxSelector.deleteCookie = function(name) {
				setCookie(name, null, new Date());
			}
	
			StrixCheckboxSelector.prototype.attachEvents=function() {
				var gs=this.selector;
				var els=document.getElementsByName(this.instanceName);
				for (var i=0; i<els.length; i++) {
					if (els[i].type=='checkbox') {
						var checkboxElement=els[i];
						(function(checkboxElement,scs) {
							StrixCheckboxSelector._addListener(checkboxElement,'change',
								function() {
									if (checkboxElement.checked) { 
										gs.select(checkboxElement.value); 
									} else { 
										gs.unselect(checkboxElement.value); 
									} 
									return false;
								}
							);
						})(checkboxElement,this);
						if (checkboxElement.disabled) {
							checkboxElement.disabled=false;
						}
						if (gs.isSelected(checkboxElement.value)) {
							if (!checkbxElement.checked) {
								checkboxElement.checked=true;
							}
						}
					}
				}
				var selectAllElement=document.getElementById(this.instanceName+'-select-all');
				if (selectAllElement) {
					if (selectAllElement.type == 'checkbox') { 
						(function (selectAllElement,scs){
							StrixCheckboxSelector._addListener(selectAllElement,'change',
									function() {
										if (selectAllElement.checked) { 
											gs.selectAll(); 
										} else {
											gs.unSelectAll(); 
										} 
										return false;			
									}
							); 
						})(selectAllElement,this);
						if (selectAllElement.disabled) {
							selectAllElement.disabled=false;
						}
						if (gs.allSelected) {
							if (!selectAllElement.checked) {
								selectAllElement.checked=true;
							}
						}
					}
				}
				var invertElement=document.getElementById(this.instanceName+'-invert');
				if (invertElement) {
					(function (e,scs){
						StrixCheckboxSelector._addListener(e,'click',
								function() {
									gs.invertAll();
									return false;			
								}
						); 
					})(invertElement,this);
				}
				var invertElement=document.getElementById(this.instanceName+'-select-none');
				if (invertElement) {
					(function (e,scs){
						StrixCheckboxSelector._addListener(e,'click',
								function() {
									gs.unSelectAll();
									return false;			
								}
						); 
					})(invertElement,this);
				}
				gs.attachEvent(
						'change', function(gsobj, item){
							if (!gsobj) return;
							var serialized=gsobj.serialize();
							StrixCheckboxSelector.setCookie('StrixCheckboxSelector_'+gsobj.instanceName,serialized);
							gsobj.debug(serialized);
						}
					); 
				gs.attachEvent(
					'select', function(gsobj, item){
						var els = document.getElementsByName(gsobj.instanceName);
						for (var i=0; i<els.length; i++) {
							if (els[i].type=='checkbox') {
								if (els[i].value == item) {
									if (!els[i].checked) {
										els[i].checked=true;
									}
								}
							}
						}
						gsobj.debug('action: select \n' + gsobj);
					}
				); 
				gs.attachEvent(
					'unselect',function(gsobj, item){
						var els = document.getElementsByName(gsobj.instanceName);
						for (var i=0; i<els.length; i++) {
							if (els[i].type=='checkbox') {
								if (els[i].value == item) {
									if (els[i].checked) {
										els[i].checked=false;
									}
								}
							}
						}
						gsobj.debug('action: unselect \n' + gsobj);
					}
				);
				gs.attachEvent(
					'selectall',function(gsobj){
						var els = document.getElementsByName(gsobj.instanceName);
						for (var i=0; i<els.length; i++) {
							if (els[i].type=='checkbox') {
								if (!els[i].checked) {
									els[i].checked=true;
								}
							}
						}
						var selectAllCheckbox=document.getElementById(gsobj.instanceName+'-select-all');
						if (selectAllCheckbox) {
							if (selectAllCheckbox.type=='checkbox') {
								if (!selectAllCheckbox.checked) {
									selectAllCheckbox.checked=true;
								}
							}
						}
						gsobj.debug('action: selectall \n' + gsobj);
					}
				);
	
				gs.attachEvent(
					'unselectall',function(gsobj){
						var els = document.getElementsByName(gsobj.instanceName);
						for (var i=0; i<els.length; i++) {
							if (els[i].type=='checkbox') {
								if (els[i].checked) {
									els[i].checked=false;
								}
							}
						}
						var selectAllCheckbox=document.getElementById(gsobj.instanceName+'-select-all');
						if (selectAllCheckbox) {
							if (selectAllCheckbox.type=='checkbox') {
								if (selectAllCheckbox.checked) {
									selectAllCheckbox.checked=false;
								}
							}
						}
						gsobj.debug('action: unselectall \n' + gsobj);
					}
				);
			}
	

		
		// end -->
		</script>
		<?php
		$ret = ob_get_contents();
		ob_end_clean();
		self::$javascriptLoaded = true;
		return $ret;
	} 

}


?>
