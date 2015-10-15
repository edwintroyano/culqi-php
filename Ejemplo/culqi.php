<?php
    define('CULQI_SDK_VERSION', '1.0.0'); class UrlAESCipher { protected $key; protected $cipher = MCRYPT_RIJNDAEL_128; protected $mode = MCRYPT_MODE_CBC; function __construct($spc479d6 = null) { $this->setBase64Key($spc479d6); } public function setBase64Key($spc479d6) { $this->key = base64_decode($spc479d6); } private function sp5dca80() { if ($this->key != null) { return true; } else { return false; } } private function spba9ac9() { return mcrypt_create_iv(16, MCRYPT_RAND); } public function urlBase64Encrypt($spf6f980) { if ($this->sp5dca80()) { $sp2f832d = mcrypt_get_block_size($this->cipher, $this->mode); $spe5d17d = UrlAESCipher::pkcs5_pad($spf6f980, $sp2f832d); $sp26599b = $this->spba9ac9(); return trim(UrlAESCipher::base64_encode_url($sp26599b . mcrypt_encrypt($this->cipher, $this->key, $spe5d17d, $this->mode, $sp26599b))); } else { throw new Exception('Invlid params!'); } } public function urlBase64Decrypt($spf6f980) { if ($this->sp5dca80()) { $spd61ade = UrlAESCipher::base64_decode_url($spf6f980); $sp26599b = substr($spd61ade, 0, 16); $spc1a9b7 = substr($spd61ade, 16); return trim(UrlAESCipher::pkcs5_unpad(mcrypt_decrypt($this->cipher, $this->key, $spc1a9b7, $this->mode, $sp26599b))); } else { throw new Exception('Invlid params!'); } } public static function pkcs5_pad($sp75c551, $sp2f832d) { $sp2d261a = $sp2f832d - strlen($sp75c551) % $sp2f832d; return $sp75c551 . str_repeat(chr($sp2d261a), $sp2d261a); } public static function pkcs5_unpad($sp75c551) { $sp2d261a = ord($sp75c551[strlen($sp75c551) - 1]); if ($sp2d261a > strlen($sp75c551)) { return false; } if (strspn($sp75c551, chr($sp2d261a), strlen($sp75c551) - $sp2d261a) != $sp2d261a) { return false; } return substr($sp75c551, 0, -1 * $sp2d261a); } protected function base64_encode_url($spcafa24) { return strtr(base64_encode($spcafa24), '+/', '-_'); } protected function base64_decode_url($spcafa24) { return base64_decode(strtr($spcafa24, '-_', '+/')); } } class Culqi { public static $llaveSecreta; public static $codigoComercio; public static $servidorBase = 'https://pago.culqi.com'; public static function cifrar($spa3b47a) { $spe550f0 = new UrlAESCipher(); $spe550f0->setBase64Key(Culqi::$llaveSecreta); return $spe550f0->urlBase64Encrypt($spa3b47a); } public static function decifrar($spa3b47a) { $spe550f0 = new UrlAESCipher(); $spe550f0->setBase64Key(Culqi::$llaveSecreta); return $spe550f0->urlBase64Decrypt($spa3b47a); } } class Pago { const URL_VALIDACION_AUTORIZACION = '/web/validar/'; const URL_ANULACION = '/anular/'; const URL_CONSULTA = '/consultar/'; const PARAM_COD_COMERCIO = 'codigo_comercio'; const PARAM_EXTRA = 'extra'; const PARAM_SDK_INFO = 'sdk'; const PARAM_NUM_PEDIDO = 'numero_pedido'; const PARAM_MONTO = 'monto'; const PARAM_MONEDA = 'moneda'; const PARAM_DESCRIPCION = 'descripcion'; const PARAM_COD_PAIS = 'cod_pais'; const PARAM_CIUDAD = 'ciudad'; const PARAM_DIRECCION = 'direccion'; const PARAM_NUM_TEL = 'num_tel'; const PARAM_INFO_VENTA = 'informacion_venta'; const PARAM_TICKET = 'ticket'; const PARAM_VIGENCIA = 'vigencia'; private static function getSdkInfo() { return array('v' => CULQI_SDK_VERSION, 'lng_n' => 'php', 'lng_v' => phpversion(), 'os_n' => PHP_OS, 'os_v' => php_uname()); } public static function crearDatospago($spa3e1d5, $sp28a2ad = null) { Pago::validateParams($spa3e1d5); $sp5f7555 = Pago::getCipherData($spa3e1d5, $sp28a2ad); $sp068955 = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp5f7555); $spda27b5 = Pago::validateAuth($sp068955); if (!empty($spda27b5) && array_key_exists(Pago::PARAM_TICKET, $spda27b5)) { $sp5aeb5a = array(Pago::PARAM_COD_COMERCIO => $spda27b5[Pago::PARAM_COD_COMERCIO], Pago::PARAM_TICKET => $spda27b5[Pago::PARAM_TICKET]); $spda27b5[Pago::PARAM_INFO_VENTA] = Culqi::cifrar(json_encode($sp5aeb5a)); } return $spda27b5; } public static function consultar($sp9d93ef) { $sp5f7555 = Pago::getCipherData(array(Pago::PARAM_TICKET => $sp9d93ef)); $spa3e1d5 = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp5f7555); return Pago::postJson(Culqi::$servidorBase . Pago::URL_CONSULTA, $spa3e1d5); } public static function anular($sp9d93ef) { $sp5f7555 = Pago::getCipherData(array(Pago::PARAM_TICKET => $sp9d93ef)); $spa3e1d5 = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp5f7555); return Pago::postJson(Culqi::$servidorBase . Pago::URL_ANULACION, $spa3e1d5); } private static function getCipherData($spa3e1d5, $sp28a2ad = null) { $spb42a70 = array_merge(array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio), $spa3e1d5); if (!empty($sp28a2ad)) { $spb42a70[Pago::PARAM_EXTRA] = $sp28a2ad; } $spb42a70[Pago::PARAM_SDK_INFO] = Pago::getSdkInfo(); $sp3534e1 = json_encode($spb42a70); return Culqi::cifrar($sp3534e1); } private static function validateAuth($spa3e1d5) { return Pago::postJson(Culqi::$servidorBase . Pago::URL_VALIDACION_AUTORIZACION, $spa3e1d5); } private static function validateParams($spa3e1d5) { if (!isset($spa3e1d5[Pago::PARAM_MONEDA]) or empty($spa3e1d5[Pago::PARAM_MONEDA])) { throw new InvalidParamsException('[Error] Debe existir una moneda'); } else { if (strlen(trim($spa3e1d5[Pago::PARAM_MONEDA])) != 3) { throw new InvalidParamsException('[Error] La moneda debe contener exactamente 3 caracteres.'); } } if (!isset($spa3e1d5[Pago::PARAM_MONTO]) or empty($spa3e1d5[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] Debe existir un monto'); } else { if (is_numeric($spa3e1d5[Pago::PARAM_MONTO])) { if (!ctype_digit($spa3e1d5[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] El monto debe ser un número entero, no flotante.'); } } else { throw new InvalidParamsException('[Error] El monto debe ser un número entero.'); } } } private static function postJson($sp754e92, $spa3e1d5) { $spf38b66 = array('http' => array('header' => "Content-Type: application/json\r\n" . "User-Agent: php-context\r\n", 'method' => 'POST', 'content' => json_encode($spa3e1d5), 'ignore_errors' => true), 'ssl' => array('CN_match' => 'www.culqi.com')); $sp14ddd4 = stream_context_create($spf38b66); $spda27b5 = file_get_contents($sp754e92, false, $sp14ddd4); $sp594441 = Culqi::decifrar($spda27b5); return json_decode($sp594441, true); } } class InvalidParamsException extends Exception { }