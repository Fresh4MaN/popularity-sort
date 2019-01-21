<?
use Bitrix\Main\Context;

class PopularSort extends CBitrixComponent{
	
	public function executeComponent(){

        $request = Context::getCurrent()->getRequest();
        if($request['SORT_FIELD'] == $this->arParams['SORT_FIELD'])
            $this->arResult['CHECKED'] = 'Y';
		$this->includeComponentTemplate();
		
	}
	
}