<?php
// warning: this auth method still using the default storage, ie. database
// so, auth/default is a pre-requisite
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/security/authentication_method/default/User.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/security/authentication_method/default/UserGroup.class.php';

class Authentication extends AuthenticationBase {
   private $mUserInfo;

   function __construct() {
      $this->mUser = new User();
      $this->mUserInfo = new UserInfo();

      if ($this->IsLoggedIn())
         $this->mUser->mUserName = $_SESSION['username'];
      else
         $this->mUser->mUserName = Configuration::Instance()->GetValue('application', 'default_user');

      $this->FetchUserInfo();
   }

   private function FetchUserInfo() {
      // reset user
      $this->mUser->GetUser();
      $this->CopyUserInfo($this->mUser);
   }

   private function CopyUserInfo($user) {
      $this->mUserInfo->mUserId = $this->mUser->mUserId;
      $this->mUserInfo->mUserName = $this->mUser->mUserName;
      $this->mUserInfo->mRealName = $this->mUser->mRealName;
      $this->mUserInfo->mPassword = $this->mUser->mPassword;
      $this->mUserInfo->mNoPassword = $this->mUser->mNoPassword;
      $this->mUserInfo->mActive = $this->mUser->mActive;
      $this->mUserInfo->mForceLogout = $this->mUser->mForceLogout;
      $this->mUserInfo->mApplicationId = $this->mUser->mApplicationId;
      //$this->mUserInfo->mActiveUserGroupId = $this->mUser->mrUserGroup->GetActiveUserGroupId(); ///FIXME: mrusergroupis using its own session value, hasrunya value ini dicopy saja ke User saat GetGroup. Atau ada ide lain?
      $this->mUserInfo->mActiveUserGroupId = $this->mUser->mActiveUserGroupId;
      $this->mUserInfo->mDefaultUserGroupId = $this->mUser->mDefaultUserGroupId;
      $this->mUserInfo->mDefaultUnitId = $this->mUser->mDefaultUnitId;
   }

   function GetCurrentUser() {
      return $this->mUserInfo;
   }

   function DoLogout($destroySession = false) {
      $this->mIsLoggedIn = FALSE;
      $_SESSION['is_logged_in'] = FALSE;
      $_SESSION['username'] = Configuration::Instance()->GetValue('application', 'default_user');
      $this->FetchUserInfo(); // reset user info
      Session::Instance()->End($destroySession);
      return true;
   }

   function DoLogin() {
      $this->mUser->mUserName = $this->mUserName;
      $this->FetchUserInfo();

      SysLog::Instance()->log('User ('.$this->mUserName.') active: ' . $this->GetCurrentUser()->GetActive(), 'login');

      if ($this->GetCurrentUser()->GetActive() != 'Yes')
         return FALSE;

      SysLog::Instance()->log('Comparing: (supplied) ' . md5($this->mPassword) . ' == (original) ' .
         $this->GetCurrentUser()->GetPassword(), 'login');
      if ($this->GetCurrentUser()->GetPassword() == md5($this->mPassword)) {
         SysLog::Instance()->log('Logged in!', 'login');
         $this->mIsLoggedIn = true;
         $_SESSION['is_logged_in'] = true;
         $_SESSION['username'] = $this->mUserName;

         Session::Instance()->Restart(); // regenerate session_id, prevent session fixation
      } else {
         $this->mIsLoggedIn = false;
      }

      return $this->mIsLoggedIn;
   }

   function CheckForceLogout() {
      if ($this->GetCurrentUser()->GetForceLogout() == 'Yes') {
         $this->DoLogout(true);
         $this->mUser->ResetForceLogout();
      }
   }

   function ForceLogout() {
      $this->mUser->ForceLogout();
   }

   function IsLoggedIn() {
      return $_SESSION['is_logged_in'] === true;
   }

   function GetLoginPage() {
      return Dispatcher::Instance()->GetUrl('login_http_auth_basic', 'LoginHttpAuthBasic', 'view', 'html') .
         '&login_first=1&back_to=' . urlencode(Configuration::Instance()->GetValue('application', 'baseaddress') .
         $_SERVER['REQUEST_URI']);
   }
}
?>