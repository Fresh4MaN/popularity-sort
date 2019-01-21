<?
use Bitrix\Main\Loader;

function countPopularity() {
    global $DB;
    Loader::includeModule("iblock");
    Loader::includeModule("sale");
    Loader::includeModule("catalog");
    Loader::includeModule("highloadblock");

    //Для ТП
    $arSKUInfo = CCatalogSKU::GetInfoByProductIBlock(CATALOG_ID);

    $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>CATALOG_ID, "ACTIVE"=>"Y"),
        false, false, Array(
            "ID", "NAME", 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PROPERTY_MORE_PHOTO', "PROPERTY_HYEAR_SHOWS", 'PROPERTY_HYEAR_NEG_SHOWS', 'PROPERTY_POPULARITY'
        )
    );
    while($arFields = $res->GetNext()){
        $V = !empty($arFields['PROPERTY_HYEAR_SHOWS_VALUE']) ? $arFields['PROPERTY_HYEAR_SHOWS_VALUE'] : 0;
        $U = !empty($arFields['PROPERTY_HYEAR_NEG_SHOWS_VALUE']) ? $arFields['PROPERTY_HYEAR_NEG_SHOWS_VALUE'] : 0;

        $P = (!empty($arFields['PREVIEW_PICTURE']) + !empty($arFields['DETAIL_PICTURE']) + count($arFields['PROPERTY_MORE_PHOTO_VALUE']) > 1);

        #Высчитываем B
        $B = 0;
        $ids = array($arFields['ID']);
        //Получаем ID торговых предложений товара
        if (is_array($arSKUInfo)) {
            $rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arSKUInfo['IBLOCK_ID'], 'PROPERTY_'.$arSKUInfo['SKU_PROPERTY_ID'] => $arFields['ID']));
            while ($arOffer = $rsOffers->GetNext()) {
                $ids[] = $arOffer['ID'];
            }
        }

        $dbBasketItems = CSaleBasket::GetList(
            array(),
            array(
                //товар или его товарные предложения
                'PRODUCT_ID' => $ids,
                '!ORDER_PAYED' => false,
                //Заказы за последний год
                ">=DATE_INSERT" => date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("n"), date("d"), date("Y")-1))
            ),
            false,
            false,
            array("QUANTITY")
        );
        while ($arItems = $dbBasketItems->Fetch()){
            $B += $arItems['QUANTITY'];
        }

        $popularity = $V + 3*$B + 10*$P - $U;

        CIBlockElement::SetPropertyValuesEx($arFields['ID'], CATALOG_ID, array('POPULARITY' => $popularity));

        //Логируем текущую итерацию
        $entity_data_class = GetEntityDataClass(HL_LOG);
        $result = $entity_data_class::add(array(
            'UF_V'         => $V,
            'UF_B'         => $B,
            'UF_P'         => $P,
            'UF_U'         => $U,
            'UF_ELEM'      => $arFields['ID'],
            'UF_DATE'      => date("d.m.Y",  time()),
        ));
    }

    return "countPopularity();";
}