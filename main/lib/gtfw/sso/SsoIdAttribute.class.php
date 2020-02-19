<?php

/**
* SSO Id Atribute Class Helper
* @package SsoClient
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class SsoIdAttribute {
   // todo make this complete
   var $mSsoLocalUsername;

   /**
   * Get identifier .. Should be this SSO system identifier
   * @return SSO identifier which can be used later to identify many SSO integrated system
   * @todo remove this will you!
   */
   function getIdentifier() {
      return "sso_academica"; // FIXME: this should be customizable via friendly config. Did I ever used? :p
   }

   /**
   * Get locally recognized username
   * @return local username provided by SSO network
   */
   function getLocalUsername() {
      return $this->mSsoLocalUsername;
   }

   /**
   * Set lcoal username .. this is a property helper
   */
   function setLocalUsername($local_username) {
      $this->mSsoLocalUsername = $local_username;
   }
}

?>