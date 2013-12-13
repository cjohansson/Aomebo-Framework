<?php
DEFINE('BASE_PATH', __DIR__.DIRECTORY_SEPARATOR);
if(file_exists(BASE_PATH.'../../../../../.mebb')){//this is just for the our framework. just ignore this. the else block is relevant for you :)
  include_once BASE_PATH.'../../../../smarty/Smarty.class.php';
  include_once BASE_PATH.'../../../../../app/core/web/smarty/functions/locale.php';
}else{
  print 'PLEASE INCLUDE YOUR SMARTY CLASS IN FILE '.__FILE__.' line '.__LINE__.' AND REMOVE THE exit() STATEMENT IN LINE '.(__LINE__ + 2).PHP_EOL;
  include realpath(BASE_PATH.'../../../mebb_i18n_smarty_function_locale.php');
  exit();
}

//1. SETUP SMARTY AS YOU USUALLY DO, i.e. DON'T JUST COPY THE BELOW, UNLESS IT FITS YOUR NEEDS
//LOAD ALL THE MODIFIERS, FILTER, ETC. THAT YOU USE IN YOUR TEMPLATES, OTHERWISE, YOU'LL
//ENCOUNTER A LOT OF COMPILE ERRORS :D
$smarty = new \Smarty();
$smarty->setTemplateDir(BASE_PATH.'templates');
$smarty->setCompileDir(BASE_PATH.'compile');
$smarty->setCacheDir(BASE_PATH.'compile');
$smarty->setConfigDir(BASE_PATH.'compile');

//2. INTEGRATE THE CUSTOM LOCALES FUNCTION. ACTUALLY THAT'S THE ONLY THING YOU REALLY HAVE TO DO
$smarty->registerPlugin('function', 'locale', '\\mebb\\app\\core\\web\\smarty\\functions\\locale');

//3. SOMEWHERE IN YOUR APPLICATION YOU SET:
//    -- THE PATH FOR THE LOCALE FILES
$smarty->assign('path', BASE_PATH.'locale');

//4. SOMEWHERE IN YOUR APPLICATION BEFORE THE FETCH/DISPLAY FUNCTION CALL
//   SET THE LOCALE TO USE. PROBABLY YOU ARE DOING THIS ANYWAYS ALREADY
//   IF YOU ARE USING GETTEXT IN YOUR PHP APPLICATION ALREADY
//   N.B.: for a tutorial consider http://onlamp.com/pub/a/php/2002/06/13/php.html
$language = (isset($argv[1])?trim($argv[1]):'de_DE');
putenv("LANG=$language"); 
setlocale(LC_ALL, $language);

//DONE! THE REST IST JUST FOR DEMONSTRATION PURPOSES
print $smarty->fetch('example_faulty_pop.tpl');

?>
