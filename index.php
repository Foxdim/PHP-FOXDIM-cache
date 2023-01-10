<?php 
/**/
include_once("Module/TxtCache.php");
$Fox_Cache = new Fox_Cache_Module();
$Fox_Cache->prefix="Prefix";

/**/
$cache_time=5;
$cache_name="HelloWorld";
$content_type="text/html; charset=UTF-8";

/*Page cache ussage*/
echo "<h3>Test Page Cache</h3><br>";
$page_cache=$Fox_Cache->Page_Cache("start",$cache_name,$cache_time,false,"post",$content_type);
if($page_cache["status"]=="success")echo $page_cache["value"];
else if($page_cache["status"]!="success")
{
/*Your codes.*/
echo date("Y-m-d H:i:s",time());
/*Stop cache.*/
$Fox_Cache->Page_Cache("fin",$cache_name,$cache_time,false,"post",$content_type);
}

echo "<br><hr><br>";
echo "<h3>Test Delegate Cache</h3><br>";
$return_data_delegate=$Fox_Cache->delegate(function(){ 
    echo date("Y-m-d H:i:s",time());
    //work return params $return_data_delegate
},$cache_name,$cache_time,false,$content_type);




?>
