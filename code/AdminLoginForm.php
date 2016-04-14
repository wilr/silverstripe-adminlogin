<?php

class AdminLoginForm extends MemberLoginForm
{

    public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true)
    {
        parent::__construct($controller, $name, $fields, $actions, $checkCurrentUser);

        if ($this->Actions()->fieldByName('forgotPassword')) {
            // replaceField won't work, since it's a dataless field
            $this->Actions()->removeByName('forgotPassword');
            $this->Actions()->push(new LiteralField(
                'forgotPassword',
                '<p id="ForgotPassword"><a href="AdminSecurity/lostpassword">'
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
     * @param array $data
     */
    public function forgotPassword($data)
    {
        $email = Convert::raw2sql($data['Email']);

        /* @var $member Member */
        $member = Member::get()->where("Email = '{$email}'")->first();

        if ($member) {
            $token = $member->generateAutologinTokenAndStoreHash();

            $this->sendPasswordResetLinkEmail($member, $token);

            $this->controller->redirect('AdminSecurity/passwordsent/' . urlencode($data['Email']));
        } elseif ($data['Email']) {
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

    /**
     * @param $member
     * @param $token
     */
    protected function sendPasswordResetLinkEmail($member, $token)
    {
        /* @var $email Member_ForgotPasswordEmail */
        $email = Member_ForgotPasswordEmail::create();
        $email->populateTemplate($member);
        $email->populateTemplate(array(
            'PasswordResetLink' => AdminSecurity::getPasswordResetLink($member, $token)
        ));
        $email->setTo($member->Email);
        $email->send();
    }

}
