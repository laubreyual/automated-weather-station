<?php

class PageController extends Controller{

	public function main(){

		$this->renderView('main');

	}

	public function renderView($page){

		$this->f3->set('template', $this->f3->get('VIEWS').$page.'.htm');
		
		echo Template::instance()->render($this->f3->get('VIEWS').'layout.htm');

	}

}
