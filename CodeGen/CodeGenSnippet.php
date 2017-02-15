<?php

class CodeGenSnippet extends Snippet {

	protected $className;
	protected $model;
	protected $storage;
	protected $snippet;
	protected $lang;

	public function run() {
		$req = $this->getRequest();

		$f = new Form();
		$e = new TextLine(array(
			FormElement::NAME => 'table', 
			FormElement::VALUE => $req['table']
		));
		$f->addElement($e);

		$e = new TextLine(array(
			FormElement::NAME => 'schema', 
			FormElement::VALUE => $req['schema']
		));
		$f->addElement($e);

		$e = new Checkbox(array(
			FormElement::NAME => 'to_file', 
			FormElement::VALUE => isset($req['to_file']) ? 1 : 0,
		));
		$f->addElement($e);


		$e = new Submit(array(
			FormElement::NAME => 'cancel', 
			FormElement::VALUE => Lang::get('Cancel')
		));
		$f->addElement($e);

		$e = new Submit(array(
			FormElement::NAME => 'generate', 
			FormElement::VALUE => Lang::get('Generate')
		));
		$f->addElement($e);

		$e = $f->getElements();
		$ret = $f->getBegin() . '
			<table>
				<tr><td>Schema name<br />(for MySQL leave emtpy)</td><td>' . $e['schema'] . '</td></tr>
				<tr><td>Table name</td><td>' . $e['table'] . '</td></tr>
				<tr><td>Save to file</td><td>' . $e['to_file'] . '</td></tr>
				<tr><td colspan="2" align="center">' . $e['generate'] . ' ' . $e['cancel'] . '</td></tr>
			</table>' . $f->getEnd();

		if ($req['generate'] && $req['table']) {

			$this->generateCode($req['schema'], $req['table']);

			$rows = 30; $cols = 120;
			$f = new Form();
			$ta = new TextArea(array(
				TextArea::NAME => 'storage', 
				TextArea::VALUE => $this->storage,
				TextArea::COLS => $cols, 
				TextArea::ROWS => $rows, 
			));
			$f->addElement($ta);

			$ta = new TextArea(array(
				TextArea::NAME => 'model', 
				TextArea::VALUE => $this->model,
				TextArea::COLS => $cols, 
				TextArea::ROWS => $rows, 
			));
			$f->addElement($ta);

			$ta = new TextArea(array(
				TextArea::NAME => 'form', 
				TextArea::VALUE => $this->snippet,
				TextArea::COLS => $cols, 
				TextArea::ROWS => $rows, 
			));
			$f->addElement($ta);

			$ta = new TextArea(array(
				TextArea::NAME => 'lang', 
				TextArea::VALUE => $this->lang,
				TextArea::COLS => $cols, 
				TextArea::ROWS => $rows, 
			));
			$f->addElement($ta);


			$e = $f->getElements();
			$ret .= $f->getBegin() . '
				<h3>Model:</h3>
				<div>' . $e['model'] . '</div>
				<h3>Storage:</h3>
				<div>' . $e['storage'] . '</div>
				<h3>Snippet:</h3>
				<div>' . $e['form'] . '</div> 
				<h3>Lang:</h3>
				<div>' . $e['lang'] . '</div>' 
				. $f->getEnd();

			if (isset($req['to_file'])) {
				$this->saveCodeToFile();
			}
		}
		return $ret;
	}

	private function generateCode($schema, $table) {
		$src = CodeGenModelSource::factory(Config::get('DB_DRIVER'));

		$src->setDb(Db::get());
		$src->setSchemaName($schema);
		$src->setTableName($table);

		$gen = new CodeGenModel(array(
			CodeGenModel::SOURCE => $src	
		));

		$this->className = $gen->getClassName();
		$this->model = $gen->generateModel();
		$this->storage = $gen->generateStorage();
		$this->snippet = $gen->generateSnippet();
		$this->lang = $gen->generateLang();
	}

	private function saveCodeToFile() {
		$className = $this->className;
		if (empty($className)) return;
		$dir = Config::get('ROOT_DIR') . 'application/lib/';

		$this->save($dir . 'storages/' . $className . 'Storage.php', $this->storage);
		$this->save($dir . 'models/' . $className . 'Model.php', $this->model);
		$this->save($dir . 'snippets/Admin' . $className . 'Snippet.php', $this->snippet);
		$this->save($dir . '../lang/model/' . $className . '.php', $this->lang);
	}

	private function save($file, $contents) {
		// creta a copy of original if has diff
		if (file_exists($file) && file_get_contents($file) != $contents) {
			copy($file, $file.'.bak');
		}
		file_put_contents($file, $contents);
	}
}
