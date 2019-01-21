<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\Context;
Loader::includeModule("iblock");

$request = Context::getCurrent()->getRequest();

//Если в сесси стоят флаги и с негативным и с позитивным просмотром, то ничего не происходит
if(empty($_SESSION['PROD_SHOWS'][$request["PRODUCT_ID"]]) || empty($_SESSION['PROD_NEG_SHOWS'][$request["PRODUCT_ID"]])) {

    $arSelect = Array("ID", 'PROPERTY_TODAY_SHOWS', 'PROPERTY_TODAY_NEG_SHOWS', 'PROPERTY_PRODUCT_ID');
    $arFilter = Array("IBLOCK_ID" => SHOWS_BLOCK_ID, "ACTIVE" => "Y", 'PROPERTY_PRODUCT_ID' => $request["PRODUCT_ID"]);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if ($arFields = $res->GetNext()) {
        //Просмотры уже были
        if ($request['TYPE'] == 'positive') {
            //За сессию товару можно только добавить один позитивный просмотр
            if(empty($_SESSION['PROD_SHOWS'][$request["PRODUCT_ID"]])) {
                //Позитивный просмотр (спустя 5 секунд) # Добавляем один позитивный и убираем один негативный, который записали ранее
                CIBlockElement::SetPropertyValuesEx($arFields['ID'], SHOWS_BLOCK_ID, array(
                        'TODAY_NEG_SHOWS' => --$arFields['PROPERTY_TODAY_NEG_SHOWS_VALUE'],
                        'TODAY_SHOWS' => ++$arFields['PROPERTY_TODAY_SHOWS_VALUE']
                    )
                );
                $_SESSION['PROD_SHOWS'][$request["PRODUCT_ID"]] = 'Y';
            }
        } else {
            //За сессию товару можно добавить только один негативный просмотр (только в случае если не был добавлен позитивный)
            if(empty($_SESSION['PROD_NEG_SHOWS'][$request["PRODUCT_ID"]]) && empty($_SESSION['PROD_SHOWS'][$request["PRODUCT_ID"]])) {
                //Негативный прссмотр (меньше 5 секунд)
                CIBlockElement::SetPropertyValuesEx($arFields['ID'], SHOWS_BLOCK_ID, array(
                        'TODAY_NEG_SHOWS' => ++$arFields['PROPERTY_TODAY_NEG_SHOWS_VALUE']
                    )
                );
                $_SESSION['PROD_NEG_SHOWS'][$request["PRODUCT_ID"]] = 'Y';
            }
        }
    } else {
        //Данный товар еще не просматривали
        $el = new CIBlockElement;
        //Запись создается всегда на негативном просмотре
        $PROP = array('TODAY_NEG_SHOWS' => 1, 'TODAY_SHOWS' => 0, 'PRODUCT_ID' => $request["PRODUCT_ID"]);
        $arLoadProductArray = Array(
            "MODIFIED_BY" => 1,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => SHOWS_BLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $request["NAME"]
        );
        if (!$el->Add($arLoadProductArray))
            AddMessage2Log($el->LAST_ERROR);
        $_SESSION['PROD_NEG_SHOWS'][$request["PRODUCT_ID"]] = 'Y';
    }
}