<?php

class AdminLoginForm extends MemberLoginForm {
	
	public function __construct($controller, $name, $fields = null, $actions = null,
								$checkCurrentUser = true) {
									
		parent::__construct($controller, $name, $fields, $actions, $checkCurrentUser);
		
		
		if($field = $this->Actions()->fieldByName('forgotPassword')) {
			// replaceField won't work, since it's a dataless field
			$this->Actions()->removeByName('forgotPassword');
			$this->Actions()->push(new LiteralField(
						'forgotPassword',
						'<p id="ForgotPassword"><a href="Security/lostpassword">'
						. _t('Member.BUTTONLOSTPASSWORD', "I've lost my password") . '</a></p>'
					));
		}
		
		Requirements::customScript(<<<JS
			(function() {
				var el = document.getElementById("AdminLoginForm_LoginForm_Email");
				if(el && el.focus) el.focus();
			})();
JS
		);
	}
	
	/**
	 *
	 */
	public function forgotPassword($data) {
		$SQL_data = Convert::raw2sql($data);
		$SQL_email = $SQL_data['Email'];
		$member = DataObject::get_one('Member', "\"Email\" = '{$SQL_email}'");
		
		$backUrlString = '';
		if(isset($data['BackURL']) && $backURL = $data['BackURL']) {
			$backUrlString = '?BackURL=' . $backURL;
		}
		
		if($member) {
			$token = $member->generateAutologinTokenAndStoreHash();

			$e = Member_ForgotPasswordEmail::create();
			$e->populateTemplate($member);
			$e->populateTemplate(array(
				'PasswordResetLink' => AdminSecurity::getPasswordResetLink($member, $token)
			));
			$e->setTo($member->Email);
			$e->send();

			$this->controller->redirect('AdminSecurity/passwordsent/' . urlencode($data['Email']));
		} elseif($data['Email']) {
			// Avoid information disclosure by displaying the same status,
			// regardless wether the email address actually exists
			$this->controller->redirect('AdminSecurity/passwordsent/' . urlencode($data['Email']));
		} else {
			$this->sessionMessage(
				_t('Member.ENTEREMAIL', 'Please enter an email address to get a password reset link.'),
				'bad'
			);
			
			$this->controller->redirect('AdminSecurity/lostpassword');
		}
	}
}
