<?php

class PageController extends Controller{

	public function main(){

		$this->render('main');

	}

	public function render($page){

		$this->f3->set('template', $this->f3->get('VIEWS').$page.'.htm');
		
		echo Template::instance()->render($this->f3->get('VIEWS').'layout.htm');

	}

}
