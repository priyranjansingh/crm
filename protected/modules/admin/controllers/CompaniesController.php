<?php

class CompaniesController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function beforeAction($action) {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (Yii::app()->user->id) {
            return true;
        } else {
            $this->redirect(CController::createUrl("/admin/login"));
        }
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $cFacility = new CFacilities;
		$cFacilityDetails = $cFacility->get_facilities($id);
        $contacts = new Contacts('compContacts');
		$contacts->unsetAttributes();  // clear any default values
        if (isset($_GET['Contacts']))
            $contacts->attributes = $_GET['Contacts'];
		
		$this->render('view', array(
            'model' => $this->loadModel($id),
            'facilities' => $cFacilityDetails
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Companies;
		$selected = "";
		if(isset($_GET['ar'])){
			$selected = $_GET['ar'];
			$comp = AR::model()->findByPk($selected);
			if($comp === null){
				throw new CHttpException(404,'Oh! Something went wrong No Account Representative Found.');
			}
		}
		$rc = "";
		if(isset($_GET['rc'])){
			$rc = $_GET['rc'];
			$comp = RC::model()->findByPk($rc);
			if($comp === null){
				throw new CHttpException(404,'Oh! Something went wrong No Rabbinical Coordinator Found.');
			}
		}
		$model->ar = $selected;
		$model->rc = $rc;
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Companies'])) {
//            pre($_FILES['Companies'], true);
            $model->attributes = $_POST['Companies'];
			
			$isFile = $_FILES['Companies']['error']['profile_pic'];
			if($isFile == 0){
				$name = $_FILES['Companies']['name']['profile_pic'];
				$type = $_FILES['Companies']['type']['profile_pic'];
				$tmp_name = $_FILES['Companies']['tmp_name']['profile_pic'];
				if(validateImageType($type) === false){
					$this->addError("profile_pic", "Please Select File Format JPG, JPEG, PNG, GIF only.");
				} else {
					$model->profile_pic = getRandomName($name);
				}
			}
            
			if ($model->save()){
                if($isFile == 0){
                    uploadCompanyProfilePic($tmp_name, $model->profile_pic);
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $prev_pic = $model->profile_pic;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Companies'])) {
            $model->attributes = $_POST['Companies'];
			$isFile = $_FILES['Companies']['error']['profile_pic'];
			if($isFile == 0){
				$name = $_FILES['Companies']['name']['profile_pic'];
				$type = $_FILES['Companies']['type']['profile_pic'];
				$tmp_name = $_FILES['Companies']['tmp_name']['profile_pic'];
				if(validateImageType($type) === false){
					$this->addError("profile_pic", "Please Select File Format JPG, JPEG, PNG, GIF only.");
				} else {
					$model->profile_pic = getRandomName($name);
				}
			} else {
				$model->profile_pic = $prev_pic;
			}
			
			if ($model->save()){
                if($isFile == 0){
                    uploadCompanyProfilePic($tmp_name, $model->profile_pic);
					if(!empty($prev_pic))
					unlink('images/companies/'.$prev_pic);
                }
				$this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }
	/**
	* Contacts Addition for companies
	**/
	
	public function actionAddcontact($id) {
		$comp = $this->loadModel($id);
		if($comp === null){
			throw new CHttpException(404,'Oh! Something went wrong No Company Found.');
		}
		
		$model = new Contacts;
		
		if(isset($_POST['Contacts'])){
			$model->attributes = $_POST['Contacts'];
			if($model->save()){
				$this->redirect(array('view','id' => $id));
			}
		}
		
		$this->render('contact',array(
			'model' => $model,
			'company' => $id;
		));
	}
	
    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('manage'));
    }
    
    public function actionRemoveFacility() {
        $facility = $_GET['id'];
        $company = $_GET['company'];
        $cf = CFacilities::model()->find(array('condition' => "company = '$company' AND facility = '$id'"));
        if($cf !== null){
            CFacilities::model()->findByPk($cf->id)->delete();
        }
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : Yii::app()->createUrl("admin/companies/view", array("id"=>$company)));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $this->redirect(array('manage'));
    }

    /**
     * Manages all models.
     */
    public function actionManage() {
        $model = new Companies('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Companies']))
            $model->attributes = $_GET['Companies'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Companies the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = Companies::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Companies $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'companies-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
