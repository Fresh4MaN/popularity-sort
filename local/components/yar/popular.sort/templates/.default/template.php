<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */
?>

<div class="col-xs-12">
    <div class="col-xs-12">
        <div class="checkbox">
            <label class="bx-filter-param-label" for="sort-popularity">
                <span class="bx-filter-input-checkbox">
                    <input type="checkbox" id="sort-popularity" <?echo $arResult['CHECKED'] == 'Y' ? 'checked' : '';?>
                        data-checked="?SORT_FIELD=<?=$arParams['SORT_FIELD']?>&SORT_ORDER=<?=$arParams['SORT_ORDER']?>"
                        data-unchecked="<?=$APPLICATION->GetCurPage(false)?>"
                    >
                    <span class="bx-filter-param-text" title="<?=$arParams['FIELD_LABEL']?>"><?=$arParams['FIELD_LABEL']?></span>
                </span>
            </label>
        </div>
    </div>
</div>
