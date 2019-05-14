<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

define('BASEPATH', '');
define('MB_ENABLED', true);
$system_path = realpath(dirname(__FILE__)  . '/system');
$application_folder = realpath(dirname(__FILE__) . '/application');

require $system_path.'/core/Lang.php';
require $system_path.'/libraries/Form_validation.php';
require $application_folder.'/third_party/ec-cube/data/class/SC_Debug.php';
require $application_folder.'/core/ORE_Array.php';
require $application_folder.'/core/ORE_Functions.php';
require $application_folder.'/core/ORE_Params.php';
require $application_folder.'/core/ORE_Fields.php';
require $application_folder.'/core/ORE_Volume.php';
require $application_folder.'/core/ORE_TreeVolume.php';
require $application_folder.'/libraries/ORE_Validation.php';
require $application_folder.'/libraries/MY_Form_validation.php';
require $application_folder.'/libraries/ORE_Data_validation.php';
require $application_folder.'/libraries/ORE_Object_validation.php';
require $application_folder.'/libraries/ORE_ExecutionTime.php';
//require $application_folder.'/libraries/ORE_FTPUpload.php';
//require $application_folder.'/libraries/ORE_HttpPost.php';
//require $application_folder.'/libraries/ORE_ImageUtil.php';

