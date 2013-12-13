<?php
/**
  * THIS FILE IS AN EXAMPLE OF HOW TO CREATE
  * ONE BIG PHP FILE FROM ALL TEMPLATES IN THE 
  * TEMPLATE DIRECTORY
  * THE CREATED PHP FILE CAN BE USED WITH with e.g. xgettext (see comment in step DONE.)
  *
  */
DEFINE('BASE_PATH', __DIR__.DIRECTORY_SEPARATOR);
include realpath(BASE_PATH.'../../../mebb_functions_glob_recursive.php');
include realpath(BASE_PATH.'../../../mebb_i18n_smarty.php');

if(file_exists(BASE_PATH.'../../../../../.mebb')){//this is just for the our framework. just ignore this. the else block is relevant for you :)
  include_once BASE_PATH.'../../../../smarty/Smarty.class.php';
  include_once BASE_PATH.'../../../../../app/core/web/smarty/functions/locale.php';
}else{
  print 'PLEASE INCLUDE YOUR SMARTY CLASS IN FILE '.__FILE__.' line '.__LINE__.' AND REMOVE THE exit() STATEMENT IN LINE '.(__LINE__ + 2).PHP_EOL;
  include realpath(BASE_PATH.'../../../mebb_i18n_smarty_function_locale.php');
  exit();
}

define('MEBB_IGNORE_ERRORS', true);
define('MEBB_TEMPLATE_EXTENSION', 'tpl');

//1.) SETUP SMARTY AS YOU USUALLY DO, i.e. DON'T JUST COPY THE BELOW, UNLESS IT FITS YOUR NEEDS
//    LOAD ALL THE MODIFIERS, FILTER, ETC. THAT YOU USE IN YOUR TEMPLATES, OTHERWISE, YOU'LL
//    ENCOUNTER A LOT OF COMPILE ERRORS :D
$smarty = new \Smarty();
$smarty->setTemplateDir(BASE_PATH.'templates');
$smarty->setCompileDir(BASE_PATH.'compile');
$smarty->setCacheDir(BASE_PATH.'compile');
$smarty->setConfigDir(BASE_PATH.'compile');

//2.) INTEGRATE THE CUSTOM LOCALES FUNCTION
$smarty->registerPlugin('function', 'locale', '\\mebb\\app\\core\\web\\smarty\\functions\\locale');

//3.) we compile all the templates in the template directory
//    you can also set a custom directory/subdirectory and only consider files therein,
//    if you like to create multiple po/mo files
$info = array();//the passback array for error definitions (in case there are any)
$sources = \mebb\lib\i18n\smarty\compile($smarty, null, $info);

//4.) we're saving the files to the temporary directory. We've chosen to save them individually
//    because the po/mo files will at least contain an indication of the origin for the
//    message IDs, even if the line # will not be correct and the file-name will
//    be re-formatted; but hey, that's as good it gets. Feedback, ideas, suggestions, etc. 
//    more than welcome
$directory = \mebb\lib\i18n\smarty\save_individual($smarty, $sources);

//DONE. The rest ist just for informational and playful purposes :
//You can no go to the directory and use any program to extract the message-IDs 
//If you are using xgettext, use the following command
// > cd /my/directory/with/translation/
// > xgettext -n *.tpl --language=PHP 

print 'The following templates have been compiled into '.$directory.':'.PHP_EOL;
foreach($sources as $source){
  print '  - '.$source['file_original'].PHP_EOL;
}

if(count($info['errors'])>0){
  print PHP_EOL.PHP_EOL.'The following errors have occured:'.PHP_EOL;
  foreach($info['errors'] as $error){
    $exception = $error['exception'];
    $file = $error['file'];
    print $file.' has the following error: '.$error['message'].PHP_EOL; 
  }
}
?>
