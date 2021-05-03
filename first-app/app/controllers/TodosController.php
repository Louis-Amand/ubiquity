<?php
namespace controllers;

use Ajax\semantic\widgets\business\user\UserModel;
use Ubiquity\attributes\items\router\Get;
use Ubiquity\attributes\items\router\Post;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Router;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\http\USession;


/**
 * Controller TodosController
 **/
class TodosController extends ControllerBase{
	const CACHE_KEY = 'datas/lists/';
	const EMPTY_LIST_ID='not saved';
	const LIST_SESSION_KEY='list';
	const ACTIVE_LIST_SESSION_KEY='active-list';

	public function initialize()
	{
		parent::initialize();
		$this->menu();
	}

	public function menu(){
		$this->loadView('todosController/menu.html');
	}


 	#[Route(path:"_default", name:'home' )]
 	public function index(){
	if(USession::exists(self::LIST_SESSION_KEY)) {
		$list = USession::get(self::LIST_SESSION_KEY);
		return $this->displayList($list);
	}
	$this->showMessage('Bienvenue !','TodoLists permet de gerer des listes...','info','info circle',    [['url'=>Router::path('todos.new'),'caption'=>'créer une nouvelle liste','style'=>'basic inverted']]);
 }
	
	private function showMessage(string $header, string $message, string $type = '', string $icon = 'info circle',array $buttons=[]) {
		$this->loadView('main/showMessage.html', compact('header', 'type', 'icon', 'message','buttons'));
		
	}

	public function displayList($list){
        if(\count($list)>0){
            $this->jquery->show('._saveList','','',false);
        }
        $this->jquery->change('#multiple', '$("._form").toggle();');
        $this->jquery->renderView('TodosController/displayList.html', ['list'=>$list]);
    }


	#[Post(path: "todos/add/", name:"todos.add")]
	public function addElement(){
		$post = URequest::post('element');
		$list = USession::get(self::LIST_SESSION_KEY);
		$list[] = $post;
		USession::set(self::LIST_SESSION_KEY, $list);
		$this->displayList($list);
	}


	#[Post(path: "todos/edit/{index}", name:"todos.edit")]
	public function editElement($index){
		$post = URequest::post('element');
		$list = USession::get(self::LIST_SESSION_KEY);
		if(isset($list[$index])){
            $list[$index] = URequest::post('editElement');
            USession::set(self::LIST_SESSION_KEY, $list);
        }
		$this->displayList($list);
	}


	

	#[Get(path: "todos/delete/{index}", name: "todos.delete")]
	public function deleteElement($index){
		$list=USession::get(self::LIST_SESSION_KEY);
        if(isset($list[$index])){
            array_splice($list, $index, 1);
            USession::set(self::LIST_SESSION_KEY, $list);
        }
        $this->displayList($list);
	}

	#[Post(path: "todos/loadList/{uniqid}", name:"todos.loadList")]
	public function loadList($uniqid){
		if (CacheManager::$cache->exists(self::CACHE_KEY . $uniqid)) {
            $list = CacheManager::$cache->fetch(self::CACHE_KEY . $uniqid);
            USession::set(self::LIST_SESSION_KEY, $list);
            $this->showMessage("Chargement","La liste ".$uniqid." à été chargée", "success", "check square outline icon");	
		}else{
			$this->showMessage('Chargement',"La liste d'id ". $uniqid ." n'existe pas", "error", "frown outline icon");
			$list = USession::get(self::LIST_SESSION_KEY);
		}
		$this->displayList($list);
	}


	#[Post(path: "todos/loadList/", name:"todos.LoadListPost")]
	public function loadListFromForm(){
		$id=URequest::post('id');
        if (CacheManager::$cache->exists(self::CACHE_KEY . $id)) {
            $list = CacheManager::$cache->fetch(self::CACHE_KEY . $id);
			$this->showMessage("Chargement","La liste ".$id." à été chargée", "success", "check square outline icon");
        }else{
            $this->showMessage('Chargement',"La liste d'id ". $id ." n'existe pas", "error", "frown outline icon");
			$list = USession::get(self::LIST_SESSION_KEY);
		}
		$this->displayList($list);
		
	}


	#[Get(path: "todos/new/{force}", name:"todos.new")]
	public function newList($force=false){
		if($force === false && USession::exists(self::LIST_SESSION_KEY)){
			return $this->showMessage('Nouvelle Liste','Une liste à déjà été crée. Souhaitez vous la vider ?','warning','info circle',    [['url'=>Router::path('todos.menu'),'caption'=>'Annuler','style'=>'basic inverted'], ['url'=>Router::path('todos.new',['MAGA']),'caption'=>'Confirmer la création','style'=>'ui green inverted button']]);
		}
		USession::set(self::LIST_SESSION_KEY,[]);
		$this->showMessage('Nouvelle Liste','Liste correctement créée.', "success", "check square outline icon");
		$this->displayList([]);
	}


	#[Get(path: "todos/saveList", name:"todos.save")]
	public function saveList(){
		$id = uniqid();
        $list=USession::get(self::LIST_SESSION_KEY);
        CacheManager::$cache->store(self::CACHE_KEY . $id, $list);

        if(USession::exists("activeUser")) {
            if (CacheManager::$cache->exists("datas/user/" . USession::get('activeUser'))) {
                $lists = CacheManager::$cache->fetch("datas/user/" . USession::get('activeUser'));
                $lists[] = $id;
                CacheManager::$cache->store("datas/user/" . USession::get('activeUser'), $lists);
                $this->showMessage("Sauvegarde", "la liste a été sauvergardée sur". USession::get('activeUser'). $id);
            } else {
                $nlists[] = $id;
                CacheManager::$cache->store("datas/user/" . USession::get('activeUser'), $nlists);
                $this->showMessage("Sauvegardé sur" . USession::get('activeUser'), $id);
            }
        }else{
            $this->showMessage("Sauvegarde", "La liste a été sauvegardée sous l'id ".$id."<br />Elle sera accessible depuis l'url <span id='url' class='ui inverted label'>https://test-1.sts-sio-caen.info/todos/loadList/".$id."</span>", "success", "check square outline icon");
        }


        $this->displayList($list);
	}

}
