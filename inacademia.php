<?php
session_start();

require 'vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

function redirect_url() {
  $URL = "http";
  $URL .=  @$_SERVER['HTTPS'] ? 's' : '';
  $URL.= '://' . $_SERVER['HTTP_HOST'] . str_replace("start.php", "redirect.php", $_SERVER['SCRIPT_NAME']);
  return $URL;
}

function inacademia_authenticate() {
  // $op_url = $_SESSION['inacademia_op_url']; // https://op.inacademia.local/
  // $scope = $_SESSION['inacademia_scope']; // student
  $op_url = "https://plugin.srv.inacademia.org/";
  $scope = 'student'; // scope is now fixed
  $client_id = $_SESSION['inacademia_client_id']; // wp
  $client_secret = $_SESSION['inacademia_client_secret']; // secret

  $oidc = new OpenIDConnectClient($op_url, $client_id, $client_secret);

  # For debug purposes on local dev
  $oidc->setVerifyHost(false);
  $oidc->setVerifyPeer(false);
  $oidc->setHttpUpgradeInsecureRequests(false);

  $oidc->addScope(explode(' ', 'transient ' . $scope));
  // $oidc->addAuthParam(array('aarc_idp_hint' => $aarc_idp_hint));
  // $oidc->addAuthParam(array('claims' => 'student'));
  // $oidc->addAuthParam(array('response_mode' => 'form_post'));
  $oidc->setResponseTypes(array('code'));
  // $oidc->setAllowImplicitFlow(true);
  $oidc->setRedirectURL( redirect_url() );

  $claims = @$_SESSION['inacademia_claims'];
  $validated = False;

  try {
    if (!$claims) {
      $oidc->authenticate();
      $claims = $oidc->getVerifiedClaims();
      if (in_array($scope, $claims->returned_scopes->values)) {
        $validated = True;
      }
    }
  } catch (Exception $e) {
    $_SESSION['inacademia_error'] = $e->getMessage();
    error_log(json_encode($e->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  $_SESSION['inacademia_validated'] = $validated;
}
