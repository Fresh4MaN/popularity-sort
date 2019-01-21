<?
use Bitrix\Main\Loader;

function SetShowsToProd() {
    Loader::includeModule("iblock");

    $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>SHOWS_BLOCK_ID, "ACTIVE"=>"Y"), false, false, Array(
            "ID", "NAME", "PROPERTY_TODAY_SHOWS", 'PROPERTY_TODAY_NEG_SHOWS', 'PROPERTY_SHOWS', 'PROPERTY_NEG_SHOWS', 'PROPERTY_PRODUCT_ID'
        )
    );
    while($arFields = $res->GetNext()){

        //формируем свойства значение=>описание
        foreach($arFields['PROPERTY_SHOWS_VALUE'] as $key => $val){
            $positive[$key] = array('VALUE' => $val, 'DESCRIPTION' => $arFields['PROPERTY_SHOWS_DESCRIPTION'][$key]);
        }
        foreach($arFields['PROPERTY_NEG_SHOWS_VALUE'] as $key => $val){
            $negative[$key] = array('VALUE' => $val, 'DESCRIPTION' => $arFields['PROPERTY_NEG_SHOWS_DESCRIPTION'][$key]);
        }

        //Присоединяем за текущий день информацию
        $positive[] = array('VALUE' =>  date("d.m.Y",  time()), 'DESCRIPTION' => $arFields['PROPERTY_TODAY_SHOWS_VALUE']);
        $negative[] = array('VALUE' => date("d.m.Y",  time()), 'DESCRIPTION' => $arFields['PROPERTY_TODAY_NEG_SHOWS_VALUE']);

        //Обновляем список показа и зануляем сегодняшние показы
        CIBlockElement::SetPropertyValuesEx($arFields['ID'], SHOWS_BLOCK_ID, array(
                'SHOWS' => $positive, 'NEG_SHOWS' => $negative, 'TODAY_SHOWS' => 0, 'TODAY_NEG_SHOWS' => 0
            )
        );

        //Просчитываем статистику за полгода
        //Поскольку у нас статистика записана посуточно, по возрастанию даты, реверсим массив и отсчитываем 182 элемента(полгода)
        $positive = array_reverse($positive);
        $negative = array_reverse($negative);

        $hfyearPos = 0;
        $hfYearNeg = 0;
        for($i = 0; $i < 182; $i++){
            if(empty($positive[$i]['DESCRIPTION']) && empty($negative[$i]['DESCRIPTION']))
                break;
            if(!empty($positive[$i]['DESCRIPTION']))
                $hfyearPos += $positive[$i]['DESCRIPTION'];
            if(!empty($negative[$i]['DESCRIPTION']))
                $hfYearNeg += $negative[$i]['DESCRIPTION'];
        }

        //Записываем в соответствующий товар значение показов за полгода
        CIBlockElement::SetPropertyValuesEx($arFields['PROPERTY_PRODUCT_ID_VALUE'], CATALOG_ID, array('HYEAR_SHOWS' => $hfyearPos, 'HYEAR_NEG_SHOWS' => $hfYearNeg));

        unset($positive, $negative, $hfyearPos, $hfYearNeg);

    }
    return "SetShowsToProd();";
}