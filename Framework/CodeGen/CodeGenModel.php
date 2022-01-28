<?php

class CodeGenModel {
	
	const SOURCE = 'source';

	protected $source;
	
	// array with columns meta data
	private $columns;
	
	protected $tableName;
	protected $className;
	protected $modelName;
	protected $storageName;
	protected $snippetName;

	function __construct($params = array()) {
		if (isset($params[self::SOURCE])) {
			$this->setSource($params[self::SOURCE]);
		}
	}
	
	public function setSource(CodeGenModelSourceInterface $source) {
		$this->source = $source;
		$this->prepareSource();
	}

	public function getSource() {
		return $this->source;
	}
	
	protected function prepareSource() {
		$this->columns = $this->source->getColumnsMeta();
		if (empty($this->columns)) {
			throw new Exception('Empty source');
		}
		foreach ($this->columns as $key => $col) {
			$name = $col[CodeGenModelSourceInterface::COL_NAME];
			
			$this->columns[$key]['name'] = $name;
			$this->columns[$key]['NAME'] = strtoupper($name);
			
			$camel = $this->toCamelCase($name);
			$this->columns[$key]['camelName'] = $camel;
			$this->columns[$key]['CamelName'] = ucfirst($camel);
		}
		$this->tableName = $this->getSource()->getTableName();
		$schema = $this->getSource()->getSchemaName();
		$this->schemaTableName = (!empty($schema) ? $schema . '.' : '') . $this->tableName;
		$this->className = ucfirst($this->toCamelCase($this->getSource()->getTableName()));
		$this->modelName = $this->className . 'Model';
		$this->storageName = $this->className . 'Storage';
		$this->snippetName = 'Admin' . $this->className . 'Snippet';
	}
	
	public function getClassName() {
		return $this->className;
	}

	public function generateStorage() {
		return $this->generateStorageClassDefinition() . '
' . $this->generateLoadMethod() . '
' . $this->generateSaveMethod() . '
' . $this->generateDeleteMethod() . '
' . $this->generateOtherMethods() . '
}
';
	}

	public function generateModel() {
		return 
	$this->generateModelClassDefinition() . '
' . $this->generateClassConstants() . '
' . $this->generateConstructor() . '
}
';		
		return $ret;
	}

	public function generateLang() {
		$ret = '<?php
	$lang  = array(';
		foreach ($this->columns as $col) {
			$ret .= '
			\'' . $col['name'] . '\' => array(
				\'HR\' => \'' . $col['name'] . '\',
				\'EN\' => \'' . ucfirst(str_replace('_', ' ', $col['name'])) . '\',
			),';
		}
		$ret .= '
	);
';
		return $ret;
}

	public function generateSnippet() {

		$ret = '<?php

class ' . $this->snippetName . ' extends Snippet {

	const NAMESP = \'' . $this->getAbbreviation($this->tableName) . 's\';

	const REQ_EDIT = \'edit\';
	const REQ_DELETE = \'delete\';
	const REQ_SAVE = \'save\';
	const REQ_CANCEL = \'cancel\';

	protected $model;
	protected $storage;
	protected $req;
	protected $nsReq;

	protected $form;
	protected $labels = array();
	protected $errors = array();

	protected static function addNamespace($var) {
		return self::NAMESP . \'[\' . $var . \']\';
	}

	public function run() {
		Lang::addDictionary(\'model/' . $this->className . '.php\');

		$this->model = new ' . $this->modelName . '();
		$this->storage = new ' . $this->storageName . '();
		$this->req = $this->getRequest();
		$this->nsReq = $this->getRequest(self::NAMESP);

		if (isset($this->nsReq[self::REQ_EDIT]) || (isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP)) {
			return $this->form();
		}
		if (is_numeric($this->nsReq[self::REQ_DELETE])) {
			return $this->delete();
		}
		return $this->grid();
	}

	protected function grid() {
		$ds = $this->storage->getDataSource();
		$cols = $this->model->getGridColumns();

		$cols = GridColumn::order($cols, array(
		';
		foreach ($this->columns as $col) {
			$ret .= "\t" . $this->modelName . '::' . $col['NAME'] . ',
		';
		}

		$ret .= '));

		$cols[] = new GridColumn(array(
			GridColumn::NAME => \'actions\',
			GridColumn::TITLE => Lang::get(\'Actions\'),
			GridColumn::SORTABLE => false,
			GridColumn::SEARCHABLE => false,
			GridColumn::FORMAT_FUNCTION => get_class() . \'::formatActions\',
		));

		$grid = new Grid(array(
			Grid::NAMESP => self::NAMESP,
			Grid::APPLICATION => $this->getApplication(),
			Grid::COLUMNS => $cols,
			Grid::DATA_SOURCE => $ds,
			Grid::DEFAULT_ORDER_BY => array('.$this->modelName.'::ID => Grid::ORDER_ASC),
		));
		return $this->renderGrid($grid);
	}

	protected function renderGrid(Grid $grid) {
		$gr = new GridRendererTable($grid);
		$newRecordUrl = $this->getApplication()->getUrl()->getSelf() . \'?\' . self::addNamespace(self::REQ_EDIT) . \'=\';
		return \'<div>
				<div class="add-new"><a href="\' . $newRecordUrl . \'">\' . Lang::get(\'Add new record\') . \'</a></div>
				<div>\' . $gr->getHtml() . \'</div>
			</div>\';
	}

	protected function delete() {
		if (!$this->storage->exists($this->nsReq[self::REQ_DELETE])) return $this->grid();
		$this->model = $this->storage->load($this->nsReq[self::REQ_DELETE]);
		$this->storage->delete($this->model);
		MessageSnippet::setMessage(Lang::get(\'Successfully deleted\'));
		Http::redirect($this->getApplication()->getUrl()->getSelf());
	}

	protected function form() {
		$this->setDefaultModelValues();

		if (isset($this->req[Form::REQ_FORM_NAME]) && $this->req[Form::REQ_FORM_NAME] == self::NAMESP) {
			$this->model->setValues($this->req);

			if (isset($this->req[self::REQ_SAVE])) {
				if (Form::isProcessed($this->req[Form::REQ_FORM_ID])) {
					Http::redirect($this->getApplication()->getUrl()->getSelf());
				}
				$this->errors = $this->model->validate();
				if (empty($this->errors)) {
					$this->model = $this->storage->save($this->model);
					Form::setProcessed($this->req[Form::REQ_FORM_ID]);
					MessageSnippet::setMessage(Lang::get(\'Successfully saved\'));
					Http::redirect($this->getApplication()->getUrl()->getSelf());
				}
			} else if (isset($this->req[self::REQ_CANCEL])) {
				Http::redirect($this->getApplication()->getUrl()->getSelf());
			}
		} else {
			$id = $this->nsReq[self::REQ_EDIT];
			if (is_numeric($id) && $this->storage->exists($id)) {
				$this->model = $this->storage->load($id);
			}
		}
		return $this->renderForm();
	}

	private function setDefaultModelValues() {
		$this->model->setValues(array(
		));	
	}

	protected function renderForm() {
		$this->generateForm();
		$e = $this->form->getElements();
		$l = $this->labels;
		$r = $this->errors;

		return $this->form->getBegin() . \'
			<table>';
		foreach ($this->columns as $col) {
			if ($col['NAME'] != 'ID') {
			$ret .= '
				<tr><td>\' . $l['.$this->modelName.'::'.$col['NAME'].'] . \'</td><td>\' . $e['.$this->modelName.'::'.$col['NAME'].'] . Util::validationError($r['.$this->modelName.'::'.$col['NAME'].']) . \'</td></tr>';
			}
		}
		$ret .=	'
				<tr><td colspan="2" align="center">\' . $e[self::REQ_SAVE] . $e[self::REQ_CANCEL] . \'</td></tr>
			</table>\' . $this->form->getEnd();
	}

	public static function formatActions($args) {
		$url = $args[Grid::FF_GRID]->getApplication()->getUrl()->getSelf();
		$ret = \'<a href="\'. $url . \'?\' . self::addNamespace(self::REQ_EDIT) . \'=\' . $args[Grid::FF_ROW]['.$this->modelName.'::ID] . \'">\' . Lang::get(\'Edit\') . \'</a>&nbsp;\';
		$ret .= \'<a href="\'. $url . \'?\' . self::addNamespace(self::REQ_DELETE) . \'=\' . $args[Grid::FF_ROW]['.$this->modelName.'::ID] . \'" onclick="return confirm(\\\'\'.Lang::get(\'Are you sure you want to delete this record?\').\'\\\');">\' . Lang::get(\'Delete\') . \'</a>\';
		return \'<div style="text-align:center">\' . $ret . \'</div>\';
	}
';

		$ret .=  $this->generateSnippetForm();
		$ret .= '
}
';

		return $ret;
	}

	protected function generateSnippetForm() {
		$ret = '
	protected function generateForm() {
		$this->form = new Form(array(
			Form::NAME => self::NAMESP
		));
';
		foreach ($this->columns as $col) {
			$ret .= $this->generateFormElement($col);
		}
		$ret .= '
		$e = new Submit(array(
			FormElement::NAME => self::REQ_CANCEL, 
			FormElement::VALUE => Lang::get(\'Cancel\')
		));
		$this->form->addElement($e);

		$e = new Submit(array(
			FormElement::NAME => self::REQ_SAVE, 
			FormElement::VALUE => Lang::get(\'Save\')
		));
		$this->form->addElement($e);
	}
';

		return $ret;
	}
	
	protected function generateFormElement($col) {
		$formElementClass = $this->getFormElementClass($col);
		$ret = '
		$e = new ' . $formElementClass . '(array(
			FormElement::NAME => '.$this->modelName.'::'.$col['NAME'].',
			FormElement::VALUE => $this->model->getProperty('.$this->modelName.'::'.$col['NAME'].')->getValue(),
			FormElement::REQUIRED => !$this->model->getProperty('.$this->modelName.'::'.$col['NAME'].')->getNullable()
		));
		$this->form->addElement($e);
';
		if ($this->classHasLabel($formElementClass)) {
			$ret .= '
		$this->labels['.$this->modelName.'::'.$col['NAME'].'] = new Label(array(
			Label::TEXT => $this->model->getProperty('.$this->modelName.'::'.$col['NAME'].')->getTitle(),
			Label::FOR_ELEMENT => $e	
		)); 
';
		}	
		return $ret;
	}


	protected function generateStorageClassDefinition() {
		$ret = '<?php

class ' . $this->storageName . ' extends StoragePdo {
		';
		
		return $ret;
	}

	protected function generateModelClassDefinition() {
		$ret = '<?php
		
class ' . $this->modelName . ' extends Model {
		';
		
		return $ret;
	}
	
	
// model 
	protected function generateClassConstants() {
		$ret = '';
		foreach ($this->columns as $col) {
			$ret .= "	const " . $col['NAME'] . " = '" . $col['name'] . "';\n"; 
		}
		return $ret;
	}
	
	protected function generateConstructor() {
		$ret = '	public function __construct() {
                $this->initProperties(array(';
		foreach ($this->columns as $col) {
                        $ret .= '
			self::'.$col['NAME'].' => new '.$this->getPropertyClass($col).'(array(
                                ModelProperty::NAME => self::'.$col['NAME'].',
                                ModelProperty::TITLE => Lang::get(\''.$col['name'].'\'),';
			if ($col['name'] != 'id') {
                                $ret .= '
				ModelProperty::NULLABLE => '.($col[CodeGenModelSourceInterface::COL_IS_NULLABLE] ? 'true':'false') . ',';
			}
			$ret .= '
                        )),';
		}
		$ret .= '
                ));
	}
';
		return $ret;
	}
	
	
// storage
	protected function generateLoadMethod() {
		return 
'	protected function getQuery() {
		return "select * from ' . $this->schemaTableName . '";
	}

	public function load($id) {
                $s = $this->getStatement($this->getQuery() . " where id = ?");
                $s->execute(array($id));

                if ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                        $m = new '.$this->modelName.'();
                        $m->setValues($row);
                        return $m;
                }
                throw new Exception(\'Missing record with id \' . $id);
        }
';
	}
	
	protected function generateSaveMethod() {
		$props = array();
		$modelConst = array();
		$pairs = array();
		foreach ($this->columns as $col) {
			if ($col['name'] != 'id') {
				$props[':'.$col['name']] = $col['name'];			
				$modelConst[] = $this->modelName . '::' . $col['NAME'];
				$pairs[] = $col['name'] . ' = :' . $col['name'];  
			}
		}

		$ret =
'	public function save(Model $model) {
                $id = $model->getProperty('.$this->modelName.'::ID)->getValue();
                return empty($id) ? $this->insert($model) : $this->update($model);
        }

        protected function insert('.$this->modelName.' $model) {
                $s = $this->getStatement("insert into '.$this->schemaTableName.' ('.implode(', ', array_values($props)).') values ('.implode(', ', array_keys($props)).')");
                $s->execute($this->getPdoParams($model->getValues(array('.implode(', ', $modelConst).'))));
                $model->getProperty('.$this->modelName.'::ID)->setValue($this->getLastInsertId('.$this->modelName.'::ID, \''.$this->tableName.'\'));
                return $model;
        }

        protected function update('.$this->modelName.' $model) {
                $s = $this->getStatement("update '.$this->schemaTableName.' set '.implode(', ', $pairs).' where id = :id");
                $s->execute($this->getPdoParams($model->getValues(array('.implode(', ', $modelConst).', '.$this->modelName.'::ID))));
                return $model;
        }
';		
		return $ret;
	}

	protected function generateDeleteMethod() {
		return 
'	public function delete(Model $model) {
                if ($model instanceof '.$this->modelName.') {
                        $s = $this->getStatement("delete from '.$this->schemaTableName.' where id = :id");
                        $s->execute($this->getPdoParams($model->getValues(array('.$this->modelName.'::ID))));
		} else {
			throw new Exception(\'Invalid model given\');
		}
        }
';		
	}	
	
	protected function generateOtherMethods() {
		return 
'	public function exists($id) {
		if (!$id) return false;
		$s = $this->getStatement("select count(id) from '.$this->schemaTableName.' where id = ?");
		$s->execute(array($id));
		return ($s->fetchColumn() > 0);
	}

        public function getDataSource() {
		return new DataSourcePdo(Db::get(), $this->getQuery());
	}
';	
	}
	

// helper methods
	protected function getPropertyClass($col) {
		switch ($col[CodeGenModelSourceInterface::COL_TYPE]) {
			case CodeGenModelSourceInterface::TYPE_TEXT: return 'TextModelProperty';
			case CodeGenModelSourceInterface::TYPE_NUMERIC: return 'NumericModelProperty';
			case CodeGenModelSourceInterface::TYPE_BOOLEAN: return 'BooleanModelProperty';
			case CodeGenModelSourceInterface::TYPE_DATE: return 'DateModelProperty';
			default: return 'TextModelProperty';
		}
	}

	protected function getFormElementClass($col) {
		if ($col['name'] == 'id') return 'Hidden';
		switch ($col[CodeGenModelSourceInterface::COL_TYPE]) {
			case CodeGenModelSourceInterface::TYPE_BOOLEAN: return 'Checkbox';
			case CodeGenModelSourceInterface::TYPE_DATE: return 'DatePicker';
			case CodeGenModelSourceInterface::TYPE_NUMERIC:
			case CodeGenModelSourceInterface::TYPE_TEXT:
			default: return 'TextLine';
		}
	}

	protected function classHasLabel($className) {
		$labeledClasses = array('TextLine', 'TextArea', 'DatePicker', 'Checkbox');
		return in_array($className, $labeledClasses);
	}

	// converts code_tag to codeTag
	protected function toCamelCase($string) {
		$words = explode('_', $string);
		
		$ret = array_shift($words); // do not ucfirst first word
		foreach ($words as $w) {
			$ret .= ucfirst($w);
		}
		return $ret;
	}
	
	// converts code_tag to ct
	protected function getAbbreviation($string) {
		$words = explode('_', strtolower($string));
		
		$ret = '';
		foreach ($words as $w) {
			$ret .= substr($w, 0, 1);
		}
		return $ret;
	}
}
