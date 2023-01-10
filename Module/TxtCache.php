<?php 
class Fox_Cache_Module {
	
public $prefix="";
private $system_control=false;
private $page_caching=false;

function __construct() {
    $this->getset_txt_cache_clear(); //call class and clear cache
}

function Page_Cache($type="start",$name="",$seconds=30,$crypto=false,$block_type="none",$contentType="text/html; charset=UTF-8")//none - get - post - getpost
{
	$return_array=[
	"status"=>"fail",
	"info"=>"err",
	"value"=>""
	
	];
	
	$cache_is_okey=true;
	if($cache_type=="get"||$cache_type=="getpost"){if(isset($_GET))$cache_is_okey=false;}
	if($cache_type=="post"||$cache_type=="getpost"){if(isset($_POST))$cache_is_okey=false;}
	
	if($cache_is_okey==false){
	$return_array=[
	"status"=>"fail",
	"info"=>"Blocked Type Cache [".$block_type."]",
	"Content-type"=>"",
	"value"=>""
	
	];
	return $return_array;
	}
	
	$name.=$_SERVER['REQUEST_URI'];
	$cache_prefix=$this->prefix;
	$this->prefix="page_".$cache_prefix;
		
	if($type=="start"&&$this->page_caching==false)
	{
	$control_data=$this->getset_txt("get",$name,"",$seconds,$crypto,$contentType);
	$this->prefix=$cache_prefix;
	if($control_data["status"]=="success"){$this->page_caching=false;
	$return_array=[
	"status"=>"success",
	"info"=>"Date Return Cache File",
	"Content-type"=>$control_data["Content-type"],
	"value"=>$control_data["value"]
	];
	return $return_array;
	}
	else{
		
	$this->page_caching=true;
	ob_start();
	$return_array=[
	"status"=>"waiting",
	"info"=>"Caching Started",
	"Content-type"=>$contentType,
	"value"=>"Call again function on type=fin"
	];
	return $return_array;
	
		}
	}
	else
	{
		if($this->page_caching==true)
		{
		$ob_data = ob_get_contents();
		$this->getset_txt("set",$name,$ob_data,$seconds,$crypto,$contentType);$this->prefix=$cache_prefix;
		$this->page_caching=false;
		$return_array=[
	"status"=>"success",
	"info"=>"Caching Finished",
	"value"=>"Save Cache File"
	];
	return $return_array;
		}
	}
	
	
	
	return $return_array;
}


function delegate($delegate_func,$name="delegatefunc",$seconds=30,$crypto=false)
{
	$control_data=$this->getset_txt("get",$name,"",$seconds,$crypto);
	if($control_data["status"]=="success"){echo $control_data["value"]["ob"]; return $control_data["value"]["return"];}
	else {
		$cache_prefix=$this->prefix;
		$this->prefix="fnc_".$cache_prefix;
			ob_start();		
			$return_data=$delegate_func();
			$delegate_code = ob_get_contents();ob_end_clean();
			$special_array=["ob"=>$delegate_code,"return"=>$return_data];
			$this->getset_txt("set",$name,$special_array,$seconds,$crypto);
			$this->prefix=$cache_prefix;
			echo $special_array["ob"];
			return $special_array["return"];
		}
}





function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            $this->getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}
function getset_txt_cache_clear()
{
	
	$cache_folder = $_SERVER["DOCUMENT_ROOT"];
	$path1=$cache_folder."/0FoxCaches";
	$path2=$cache_folder."/0FoxCaches/FoxCaches";
	if ( !is_dir( $path1 ) ) {
	mkdir( $path1 );       
	}
	if ( !is_dir( $path2 ) ) {
	mkdir( $path2 );       
	}
	
	$cache_prefix=$this->prefix;
	$this->prefix="";
	$cache_clear_time=$this->getset_txt("getset","FoxLastCacheClearTime",time()+60,60);
	if($cache_clear_time["value"]>=time())return ""; //kontrol tarihi ayarlaması.
	
	
	
	$cache_clear_time=$this->getset_txt("set","FoxLastCacheClearTime",time()+60,60);
	$files=$this->getDirContents($path2);
	$this->prefix=$cache_prefix;
	
	foreach($files as $file)
	{
		$file_value=file_get_contents($file);
		if($file_value!==false){
			$file_data=json_decode($file_value,true);
			if($file_data["delete_time"]<=time()||$file_data["delete_time"]==null){unlink($file);}
		}
	}
}
function getset_txt($type="getset",$name,$value="",$seconds=60,$cyrpto=false,$contentType="text/html; charset=UTF-8")
{
$cache_folder = $_SERVER["DOCUMENT_ROOT"];
$return_data= ["status"=>"fail","value"=>"","info"=>"not have"];
$return_data="";

$md5_name=$this->prefix."".md5($name);



$file_name=$md5_name.".fox";
$path_final=$cache_folder."/0FoxCaches/FoxCaches";

$cache_file_path=$path_final."/".$file_name;

$file_value=file_get_contents($cache_file_path);

if($type=="get"||$type=="getset"){
if($file_value!==false){
	
	$file_data=json_decode($file_value,true);
	$new_value=base64_decode($file_data["value"]);
	if($file_data["status"]!="success"){unlink($cache_file_path);$file_value==false;}
	else if($file_data["delete_time"]<=time()){unlink($cache_file_path);}
	else if($file_data["cyrpto"]==true){
			$new_value=json_decode($this->decode($new_value),true);
	}
	$return_data= ["status"=>"success","Content-type"=>$file_data["Content-type"],"value"=>$new_value,"info"=>"old cache"];
}
else {$return_data= ["status"=>"fail","Content-type"=>"","value"=>"","info"=>"not have"];}
}

if($type=="set"||$type=="getset"){
if($file_value===false||$type=="set")
{
	$new_value=$value;
	if($cyrpto==true){
		$new_value=$this->encode(json_encode($value));
	}
	file_put_contents($cache_file_path,json_encode(["status"=>"success","Content-type"=>$contentType,"value"=>base64_encode($new_value),"delete_time"=>time()+($seconds),"cyrpto"=>$cyrpto]));
	$return_data= ["status"=>"success","Content-type"=>$contentType,"value"=>$value,"info"=>"create cache"];
	
}
}
return $return_data;
}

/*Foxdim crypto cache module*/
private $org_public_key="ec&TTlSx%&l763Yc";
private $org_private_key='xd8%Sv9Q&rH3IR3i';
private $public_key='';
private $private_key='';
private $ciphering = "AES-128-CBC";
function encode($text,$private="",$public="")
{
	$ciphering=$this->ciphering;
    $public_key=$this->public_key;
    $private_key=$this->private_key;
	if(strlen($private)==16&&strlen($public)==16)
	{
	$public_key=$private;
    $private_key=$public;
	}
$iv_length = openssl_cipher_iv_length($ciphering);
$encryption = openssl_encrypt($text, $ciphering,
$public_key, 0, $private_key);
return $encryption;
}

function decode($text,$private="",$public="")
{
    $ciphering=$this->ciphering;
    $public_key=$this->public_key;
    $private_key=$this->private_key;
	if(strlen($private)==16&&strlen($public)==16)
	{
	$public_key=$private;
    $private_key=$public;
	}
$iv_length = openssl_cipher_iv_length($ciphering);
$decryption=openssl_decrypt ($text, $ciphering, 
$public_key, $options, $private_key);
return $decryption;
}

}
?>
