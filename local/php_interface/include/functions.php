<?
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Loader;

function _d($var, $isAdmin = false, $caller = null){

    global $USER;
    if(!($USER->IsAdmin() || $isAdmin))
        return false;
    if(!isset($caller)){
        $caller = array_shift(debug_backtrace(1));
    }

    echo '<code>File: '.$caller['file'].' / Line: '.$caller['line'].'</code>';
    echo '<pre>';
    echo print_r($var, true);
    echo '</pre>';
}

function getIBByCode($code){
    Loader::IncludeModule('iblock');
    $resc = CIBlock::GetList(Array(), Array('CODE' => $code, 'CHECK_PERMISSIONS' => 'N'), false);
    if($arrc = $resc->Fetch())
        return $arrc["ID"];
}

function getIBElementByCode($code){
    Loader::IncludeModule('iblock');
    $resc = CIBlockElement::GetList(Array(), Array('CODE' => $code, 'CHECK_PERMISSIONS' => 'N'), false);
    if($arrc = $resc->Fetch())
        return $arrc["ID"];
}

function getSectionByCode($iblock_id, $code){
    Loader::IncludeModule('iblock');
    $res = CIBlockSection::GetList(array(),array('IBLOCK_ID'=>$iblock_id,'CODE'=>$code));
    if($section = $res->Fetch())
        return $section['ID'];
    else
        return false;
}

function GetEntityDataClass($HlBlockId) {
    Loader::includeModule("highloadblock");
    if (empty($HlBlockId) || $HlBlockId < 1)
    {
        return false;
    }
    $hlblock = HLBT::getById($HlBlockId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    return $entity_data_class;
}