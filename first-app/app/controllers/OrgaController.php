<?php
 namespace controllers;
 use models\Groupe;
 use models\Organization;
 use Ubiquity\attributes\items\router\Get;
 use Ubiquity\attributes\items\router\Post;
 use Ubiquity\attributes\items\router\Route;
 use Ubiquity\controllers\Router;
 use Ubiquity\orm\DAO;
 use Ubiquity\orm\repositories\ViewRepository;
 use Ubiquity\utils\http\URequest;
 use Ubiquity\utils\http\UResponse;

 /**
  * Controller OrgaController
  */
class OrgaController extends ControllerBase{

    private ViewRepository $repo;

    public function initialize(){
        parent::initialize();
        $this->repo = new ViewRepository($this, Organization::class);
    }

    #[Route(path : 'orga', name: 'orga.menu')]
	public function index(){
        $this->repo->all("",false);
		$this->loadView("OrgaController/index.html");
	}

	#[Route(path: "getOne/{idOrga}",name: "orga.getOne")]
	public function getOne($idOrga){
        $this->repo->byId($idOrga,['users.groupes', 'groupes.users']);
        //$orga =DAO::getById(Organization::class,$idOrga,['users.groupes','groupes.users']);
		$this->loadDefaultView();
	}

    #[Route(path : 'orga/add', name : "orga.addOrgaForm")]
    public function addOrgaForm(){
        if(URequest::filled('valid')){
            if (URequest::filled('name') && URequest::filled('domain')){
                $orga=new Organization();
                $orga->setName(URequest::post('name'));
                $orga->setDomain(URequest::post('domain'));
                $orga->setAliases(URequest::post('alias'));
                URequest::setValuesToObject($orga);
                if(DAO::insert($orga)){
                    echo 'Nouvelle organisation insérée';
                }
            }else{
                echo 'Remplir les champs';
            }
        }
        $this->loadView("OrgaController/orgaForm.html",['title'=> 'Ajouter Organisation', 'route'=>'orga.addOrgaForm']);
    }

    #[Route(path : 'orga/update/{idOrga}', name : "orga.updateOrga")]
    public function updateOrga($idOrga){
        echo $idOrga;
        $this->loadView("OrgaController/orgaForm.html",['title'=> 'Mettre à jour organisation', 'route'=>'orga.updateOrgaForForm','idOrga'=>$idOrga]);
    }

    #[Post(path : 'orga/update', name : "orga.updateOrgaForForm")]
    public function updateOrgaForForm(){
        print_r($_POST);

        $orga=DAO::getById(Organization::class,URequest::post('id'));
        $orga->setName(URequest::post('name'));
        $orga->setDomain(URequest::post('domain'));
        $orga->setAliases(URequest::post('alias'));
        URequest::setValuesToObject($orga);
        if(DAO::update($orga)){
            UResponse::header('location','/'.Router::path('orga.menu'));
        }
    }

    #[Route(path : 'orga/delete/{idOrga}', name : "orga.delete")]
    public function delete($idOrga){
        echo $idOrga;
        $this->loadView("OrgaController/confirmation.html",['title'=> 'Supprimer', 'idOrga'=>$idOrga]);
    }

    #[Route(path : 'orga/delete', name : "orga.deletePost")]
    public function deletePost(){
        if(DAO::delete(Organization::class,URequest::post('id'))){
            UResponse::header('location','/'.Router::path('orga.menu'));
        }
    }

    #[Route(path : 'orga/addGroup', name : "orga.addGroup")]
    public function addGroup(){
        $this->repo->all("",false);
        $this->loadView("OrgaController/orgaGroupForm.html");
    }

    #[Post(path : 'orga/addGroupForm', name : "orga.addGroupForm")]
    public function addGroupForm(){
        $group=new Groupe();
        $group->setAliases(URequest::post('alias'));
        $group->setEmail(URequest::post('email'));
        $group->setName(URequest::post('name'));
        URequest::setValuesToObject($group);
        $orga=DAO::getById( Organization::class, URequest::post('organization'));
        $group->setOrganization($orga);
        DAO::insert($group);
        UResponse::header('location','/'.Router::path('orga.menu'));
    }

}