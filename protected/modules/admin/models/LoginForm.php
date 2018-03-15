<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel {

    public $username;
    public $password;
    public $rememberMe;
    private $_identity;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            // username and password are required
            array('username, password', 'required'),
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('password', 'login'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'rememberMe' => 'Remember me next time',
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
//	public function authenticate($attribute,$params)
//	{
//		if(!$this->hasErrors())
//		{
//			$this->_identity=new UserIdentity($this->username,$this->password);
//			if(!$this->_identity->authenticate())
//				$this->addError('password','Incorrect username or password.');
//		}
//	}
    /*public function authenticate($attribute, $params) {
        if (!$this->hasErrors()) {
            $this->_identity = new UserIdentity($this->username, $this->password);
            if (!$this->_identity->authenticate())
                $this->addError('password', 'Incorrect username or password.');
                $this->addError('username', 'Incorrect username or password.');
        }
    }*/

    
    public function authenticate($attribute, $params) {
        if (!$this->hasErrors()) {  // we only want to authenticate when no input errors
            $identity = new UserIdentity($this->username, $this->password);
            $identity->authenticate();
            switch ($identity->errorCode) {
                case UserIdentity::ERROR_NONE:
                    $duration = $this->rememberMe ? Yii::app()->controller->module->rememberMeTime : 0;

                    Yii::app()->user->login($identity, $duration);
                    break;
                case UserIdentity::ERROR_EMAIL_INVALID:
                    $this->addError("username", "Email is incorrect.");
                    break;
                case UserIdentity::ERROR_USERNAME_INVALID:
                    $this->addError("username", "Username/Password is incorrect.");
                    break;
                case UserIdentity::ERROR_STATUS_NOTACTIV:
                    $this->addError("status", "Your account is not activated.");
                    break;
                case UserIdentity::ERROR_STATUS_BAN:
                    $this->addError("status", "Your account is blocked.");
                    break;
                case UserIdentity::ERROR_PASSWORD_INVALID:
                    $this->addError("password", "Username/Password is incorrect.");
                    break;
            }
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login() {
        if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->username, $this->password);
            $this->_identity->authenticate();
            switch ($this->_identity->errorCode) {
                case UserIdentity::ERROR_NONE:
                    $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
                    Yii::app()->user->login($this->_identity, $duration);
                    return true;
                    break;
                case UserIdentity::ERROR_EMAIL_INVALID:
                    $this->addError("username", "Email is incorrect.");
                    return false;
                    break;
                case UserIdentity::ERROR_USERNAME_INVALID:
                    $this->addError("username", "Username/Password is incorrect.");
                    return false;
                    break;
                case UserIdentity::ERROR_STATUS_BAN:
                    $this->addError("status", "Your account is blocked.");
                    return false;
                    break;
                case UserIdentity::ERROR_PASSWORD_INVALID:
                    $this->addError("password", "Username/Password is incorrect.");
                    return false;
                    break;
            }
        }
//        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
//            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
//            Yii::app()->user->login($this->_identity, $duration);
//            return true;
//        } else
//            return false;
    }

}
