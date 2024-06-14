<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заявки на присвоение классных чинов");

if (!$GLOBALS["arrFilter"]) $GLOBALS["arrFilter"] = array();

require_once('rank_duration.php');

$listId = 'ranking_list';
$excelDownload = ($_GET["IFRAME"] =='Y') && ($_GET["EXCEL"] =='DOWNLOAD');

$grid_options = new Bitrix\Main\Grid\Options($listId);
$sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$navParams = $grid_options->GetNavParams();

$nav = new Bitrix\Main\UI\PageNavigation($listId);
$nav->allowAllRecords(true)
    ->setPageSize($navParams['nPageSize'])
    ->initFromUri();
if ($nav->allRecordsShown()) {
	$navParams = false;
} else {
	$navParams['iNumPage'] = $nav->getCurrentPage();
}

$uiFilter = [
	['id' => 'ID', 'name' => 'ID', 'type'=>'number', 'default' => false],
	['id' => 'NAME', 'name' => 'Ф.И.О.', 'type'=>'text', 'default' => true],
	['id' => 'ACTIVE', 'name' => 'Активность', 'type'=>'list', "items" => array("Y" => "Да","N" => "Нет"), 'default' => false],
	['id' => 'DATE_CREATE', 'name' => 'Дата создания', 'type'=>'date', 'default' => false],
	['id' => 'TIMESTAMP_X', 'name' => 'Дата изменения', 'type'=>'date', 'default' => false],
	['id' => 'CREATED_BY', 'name' => 'Кем создана', 'type'=>'number', 'default' => false],
];

$snippets = new \Bitrix\Main\Grid\Panel\Snippet();
$editButton = $snippets->getEditButton();
$removeButton = $snippets->getRemoveButton();
$forAllCheckbox = $snippets->getForAllCheckbox();
// $typeList = \Bitrix\Main\Grid\Panel\Types::getList();
// $actionsList = \Bitrix\Main\Grid\Panel\Actions::getList();
// echo Bitrix\Main\Grid\Panel\Types::CHECKBOX;
// echo Bitrix\Main\Grid\Panel\Types::DROPDOWN;

$columns = [];
$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'editable'=> false, 'type'=>'number', 'default' => false];
$columns[] = ['id' => 'NAME', 'name' => 'Ф.И.О.', 'sort' => 'NAME', 'editable'=> true, 'type'=>'text', 'default' => true];
$columns[] = ['id' => 'ACTIVE', 'name' => 'Активность', 'sort' => 'ACTIVE', 'editable'=> true, 'type'=>'checkbox', "items" => array("Y" => "Да","N" => "Нет"), 'default' => false];
$columns[] = ['id' => 'DATE_CREATE', 'name' => 'Дата создания', 'sort' => 'DATE_CREATE', 'editable'=> false, 'type'=>'date', 'default' => false];
$columns[] = ['id' => 'TIMESTAMP_X', 'name' => 'Дата изменения', 'sort' => 'TIMESTAMP_X', 'editable'=> false, 'type'=>'date', 'default' => false];
$columns[] = ['id' => 'CREATED_USER_NAME', 'name' => 'Создатель', 'sort' => 'CREATED_USER_NAME', 'editable'=> false, 'type'=>'text', 'default' => false];
$columns[] = ['id' => 'CREATED_BY', 'name' => 'Кем создана', 'sort' => 'CREATED_BY', 'editable'=> false, 'type'=>'number', 'default' => false];

$arSelect = ["*"];
// $customerProperties = array("PROGRAM_CODE","PERFORMER_PLAN","PRIORITY_DIRECTION","TEACHING_FORM","PROGRAM_VOLUME","AUDIENCE_TARGET","QUOTA_AMOUNT","QUOTA_TOTAL","DATE_BEGIN","");
// $hiddenProperties = ['TEACHING_FLOW', 'STUDY_FINISH', 'ORDER_NUMBER', 'ORDER_DATE', 'ORDER_NOTATION', 'PROGRAM_DESCRIPTION', 'TEACHING_FLOW'];
$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=> 53));
while ($propFields = $properties->GetNext()) {
	$arSelect[] = 'PROPERTY_'.$propFields["CODE"];
	// if (in_array($propFields["CODE"], $customerProperties)) {
		if ($propFields["PROPERTY_TYPE"] == "N") {
			$uiFilter[] = ['id' => 'PROPERTY_'.$propFields["CODE"], 'name' => $propFields["NAME"], 'type'=>'number', 'default' => false];
			$columns[] = ['id' => 'PROPERTY_'.$propFields["CODE"].'_VALUE', 'name' => $propFields["NAME"], 'sort' => 'PROPERTY_'.$propFields["CODE"], 'editable'=> true, 'type'=>'number', 'default' => false];
		}
		if ($propFields["PROPERTY_TYPE"] == "L") {
			$infoList = []; $listGroup = array();
			$propertyEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("PROPERTY_ID" => $propFields["ID"]));
			while($arEnum = $propertyEnums->GetNext()) {
				$infoList[intval($arEnum["ID"])] = $arEnum["VALUE"];
				$listGroup[] = array("NAME" => $arEnum["VALUE"], "VALUE" => intval($arEnum["ID"]));
			}
			$uiFilter[] = ['id' => 'PROPERTY_'.$propFields["CODE"], 'name' => $propFields["NAME"], 'type'=>'list', 'items' => $infoList, 'params' => ['multiple' => 'Y'], 'default' => true];
			$columns[] = ['id' => 'PROPERTY_'.$propFields["CODE"].'_ENUM_ID', 'name' => $propFields["NAME"], 'sort' => 'PROPERTY_'.$propFields["CODE"], 'editable'=> true, 'type'=>'list', 'items' => $listGroup, 'default' => true]; // 1777
		}
		if ($propFields["PROPERTY_TYPE"] == "S" && !$propFields["USER_TYPE"]) {
			$uiFilter[] = ['id' => 'PROPERTY_'.$propFields["CODE"], 'name' => $propFields["NAME"], 'type'=>'text', 'default' => true];
			$columns[] = ['id' => 'PROPERTY_'.$propFields["CODE"].'_VALUE', 'name' => $propFields["NAME"], 'sort' => 'PROPERTY_'.$propFields["CODE"], 'editable'=> true, 'type'=>'text', 'default' => true];
		}
		if ($propFields["USER_TYPE"] == "Date") {
			$uiFilter[] = ['id' => 'PROPERTY_'.$propFields["CODE"], 'name' => $propFields["NAME"], 'type'=>'date', 'default' => true];
			$columns[] = ['id' => 'PROPERTY_'.$propFields["CODE"].'_VALUE', 'name' => $propFields["NAME"], 'sort' => 'PROPERTY_'.$propFields["CODE"], 'editable'=> true, 'type'=>'date', 'default' => true];
		}
	// }
	$arPropFields[] = $propFields;
}
// $columns[] = ['id' => 'STUDY_PERIODS', 'name' => "Периоды обучения", 'sort' => 'STUDY_PERIODS', 'editable'=> false, 'type'=>'text', 'default' => false];

$filterOption = new Bitrix\Main\UI\Filter\Options($listId);
$filterData = $filterOption->getFilter($uiFilter);
$logicFilter = \Bitrix\Main\UI\Filter\Type::getLogicFilter($filterData, $uiFilter);
$logicFilter['IBLOCK_ID'] = 53;
$logicFilter['!TAGS'] = "deletedEmployee";
if ($logicFilter['NAME']) $logicFilter['NAME'] = '%'.$logicFilter['NAME'].'%';
if ($logicFilter['PROPERTY_JOB_TITLE']) $logicFilter['PROPERTY_JOB_TITLE'] = '%'.$logicFilter['PROPERTY_JOB_TITLE'].'%';
if ($logicFilter['PROPERTY_DEPARTMENT']) $logicFilter['PROPERTY_DEPARTMENT'] = '%'.$logicFilter['PROPERTY_DEPARTMENT'].'%';
if ($logicFilter['PROPERTY_BAN_REASON']) $logicFilter['PROPERTY_BAN_REASON'] = '%'.$logicFilter['PROPERTY_BAN_REASON'].'%';
$findValue = strtolower(trim($filterData['FIND']));
if ($findValue) $logicFilter[0] = array(
		"LOGIC" => "OR", 
		"=ID" => $findValue, 
		"%NAME" => $findValue, 
		"%PROPERTY_JOB_TITLE" => $findValue, 
		"%PROPERTY_DEPARTMENT" => $findValue, 
		"%PROPERTY_BAN_REASON" => $findValue, 
	);
if ($logicFilter['<=PROPERTY_HIRING_DATE']) $logicFilter['<=PROPERTY_HIRING_DATE'] = date('Y-m-d', strtotime($logicFilter['<=PROPERTY_HIRING_DATE']));
if ($logicFilter['>=PROPERTY_HIRING_DATE']) $logicFilter['>=PROPERTY_HIRING_DATE'] = date('Y-m-d', strtotime($logicFilter['>=PROPERTY_HIRING_DATE']));
if ($logicFilter['<=PROPERTY_CURRENT_RANK_DATE']) $logicFilter['<=PROPERTY_CURRENT_RANK_DATE'] = date('Y-m-d', strtotime($logicFilter['<=PROPERTY_CURRENT_RANK_DATE']));
if ($logicFilter['>=PROPERTY_CURRENT_RANK_DATE']) $logicFilter['>=PROPERTY_CURRENT_RANK_DATE'] = date('Y-m-d', strtotime($logicFilter['>=PROPERTY_CURRENT_RANK_DATE']));
if ($logicFilter['<=PROPERTY_NOT_CLASS_RANK_DATE']) $logicFilter['<=PROPERTY_NOT_CLASS_RANK_DATE'] = date('Y-m-d', strtotime($logicFilter['<=PROPERTY_NOT_CLASS_RANK_DATE']));
if ($logicFilter['>=PROPERTY_NOT_CLASS_RANK_DATE']) $logicFilter['>=PROPERTY_NOT_CLASS_RANK_DATE'] = date('Y-m-d', strtotime($logicFilter['>=PROPERTY_NOT_CLASS_RANK_DATE']));
if ($logicFilter['<=PROPERTY_TRIAL_PERIOD_END']) $logicFilter['<=PROPERTY_TRIAL_PERIOD_END'] = date('Y-m-d', strtotime($logicFilter['<=PROPERTY_TRIAL_PERIOD_END']));
if ($logicFilter['>=PROPERTY_TRIAL_PERIOD_END']) $logicFilter['>=PROPERTY_TRIAL_PERIOD_END'] = date('Y-m-d', strtotime($logicFilter['>=PROPERTY_TRIAL_PERIOD_END']));
if ($logicFilter['<=PROPERTY_BIRTH_DATE']) $logicFilter['<=PROPERTY_BIRTH_DATE'] = date('Y-m-d', strtotime($logicFilter['<=PROPERTY_BIRTH_DATE']));
if ($logicFilter['>=PROPERTY_BIRTH_DATE']) $logicFilter['>=PROPERTY_BIRTH_DATE'] = date('Y-m-d', strtotime($logicFilter['>=PROPERTY_BIRTH_DATE']));

$arFilter = array_merge($GLOBALS["arrFilter"], $logicFilter);
if ($excelDownload) $navParams = false;
$res = \CIBlockElement::GetList($sort['sort'], $arFilter, false, $navParams, $arSelect);


if ($excelDownload) { 
    $arExcel = $arValue = array(); 
    while ($rowView = $res->GetNext()) {
		
        $arValue = array(
            'Ф.И.О.' => $rowView["NAME"], 
            'Код' => $rowView["PROPERTY_CO_WORKER_VALUE"], 
            'Дата приема на работу' => $rowView["PROPERTY_HIRING_DATE_VALUE"],  
            'Должность' => $rowView["PROPERTY_JOB_TITLE_VALUE"], 
            'Подразделение' => $rowView["PROPERTY_DEPARTMENT_VALUE"], 
            'Текущий классный чин госслужащего' => $rowView["PROPERTY_CURRENT_RANK_NAME_VALUE"], 
            'Дата присвоения текущего классного чина госслужащего' => $rowView["PROPERTY_CURRENT_RANK_DATE_VALUE"], 
            'Срок до следующего классного чина госслужащего' => $rowView["PROPERTY_NEXT_RANK_TIME_VALUE"], 
            'Наименование следующего классного чина госслужащего' => $rowView["PROPERTY_NEXT_RANK_NAME_VALUE"], 
            'Статус заявки' => $rowView["PROPERTY_REQUEST_STATUS_VALUE"],
        );
        $arExcel[] = $arValue;
    }
    echo json_encode([$arFilter, $arExcel]);
    // echo json_encode($arExcel);
    die();
}

// echo $res->selectedRowsCount();
$nav->setRecordCount($res->selectedRowsCount());
$list = $rowItems = [];
while($row = $res->GetNext()) {
	$inlineEdit = $row;
	$row['PROPERTY_REQUEST_STATUS_ENUM_ID'] = $inlineEdit['PROPERTY_REQUEST_STATUS_VALUE'];
	$row['PROPERTY_CURRENT_RANK_NAME_ENUM_ID'] = $inlineEdit['PROPERTY_CURRENT_RANK_NAME_VALUE'];
	$row['PROPERTY_NEXT_RANK_NAME_ENUM_ID'] = $inlineEdit['PROPERTY_NEXT_RANK_NAME_VALUE'];
	$row['PROPERTY_MILITARY_RANK_ENUM_ID'] = $inlineEdit['PROPERTY_MILITARY_RANK_VALUE'];
	$row['PROPERTY_POLICE_RANK_ENUM_ID'] = $inlineEdit['PROPERTY_POLICE_RANK_VALUE'];
	$row['PROPERTY_JUSTICE_RANK_ENUM_ID'] = $inlineEdit['PROPERTY_JUSTICE_RANK_VALUE'];
	$row['PROPERTY_PROSECUTORS_RANK_ENUM_ID'] = $inlineEdit['PROPERTY_PROSECUTORS_RANK_VALUE'];
	// $row['PROPERTY_AUDIENCE_TARGET_VALUE'] = implode('; ', $row['PROPERTY_AUDIENCE_TARGET_VALUE']);
	// $studyPeriods = array();
	// foreach ($row['PROPERTY_DATE_BEGIN_VALUE'] as $num => $dateBegin) {
	// 	$studyPeriods[] = $dateBegin.' - '.$row['PROPERTY__VALUE'][$num];
	// }
	// $row['STUDY_PERIODS'] = implode('; ', $studyPeriods);
    $rowItems[] = $row;
	$list[] = [
		'columns' => $row,
		'data' => $inlineEdit,
		// 'data' => [
		// 	"ID" => $row['ID'],
		// 	"NAME" => $row['NAME'],
		// 	"DATE_CREATE" => $row['DATE_CREATE'],
		// ],
		'actions' => [
			[
				'text'    => 'Просмотр',
				'default' => true,
				'onclick' => 'document.location.href="detail.php?ELEMENT_ID='.$row['ID'].'"'
			], 
			[
				'text'    => 'Редактировать',
				'default' => true,
				'onclick' => 'document.location.href="edit.php?CODE='.$row['ID'].'"'
			], [
				'text'    => 'Удалить',
				'default' => true,
				'onclick' => 'if(confirm("Точно?"))deleteThisEmployee('.$row['ID'].');'
			]
		],
		'editable' => 'Y',
	];
}
// echo '<script type="text/javascript">console.log('.json_encode($rowItems).');</script>';
\Bitrix\Main\UI\Extension::load("ui.buttons"); 
?>
<style>
	.main-ui-filter-search {
		float: none !important;
		margin: 0 19px 0 0 !important;
		width: calc(100% - 187px);
	}
	.menu-popup-excel .menu-popup-item-icon {
		display: inline-block !important;
		margin: 0 !important;
		width: 26px;
		background: url(images/popup_menu_sprite_2.png) no-repeat 2px -1287px;
		height: 36px;
	}
</style>
<div style="display:flex;margin-bottom:20px;justify-content:space-between;">
<?$APPLICATION->IncludeComponent('mirea:main.ui.filter', '', [
	'FILTER_ID' => $listId,
	'GRID_ID' => $listId,
	'FILTER' => $uiFilter,
	'ENABLE_LIVE_SEARCH' => true,
	'ENABLE_LABEL' => true
]);?>
<div class="ui-btn-split ui-btn-primary">
	<button class="ui-btn-main" onclick="location.href='edit.php'" style="padding: 0 30px;">Добавить</button>
	<button id="menu-btn" class="ui-btn-menu" onclick="popupMenu.popupWindow.show();"></button>
</div>
</div>
<div style="clear: both;"></div>
<?$APPLICATION->IncludeComponent('mirea:main.ui.grid', '', [ 
    'GRID_ID' => $listId, 
    'COLUMNS' => $columns, 
    'ROWS' => $list, //Самое интересное, опишем ниже
    'SHOW_ROW_CHECKBOXES' => true, 
    'NAV_OBJECT' => $nav, 
    'AJAX_MODE' => 'Y', 
    'AJAX_ID' => \CAjax::getComponentID('mirea:main.ui.grid', '.default', ''), 
    'PAGE_SIZES' => [ 
        ['NAME' => "5", 'VALUE' => '5'], 
        ['NAME' => '10', 'VALUE' => '10'], 
        ['NAME' => '20', 'VALUE' => '20'], 
        ['NAME' => '50', 'VALUE' => '50'], 
        ['NAME' => '100', 'VALUE' => '100'] 
    ], 
    'AJAX_OPTION_JUMP'          => 'N', 
    'SHOW_CHECK_ALL_CHECKBOXES' => true, 
    'SHOW_ROW_ACTIONS_MENU'     => true, 
    'SHOW_GRID_SETTINGS_MENU'   => true, 
    'SHOW_NAVIGATION_PANEL'     => true, 
    'SHOW_PAGINATION'           => true, 
    'SHOW_SELECTED_COUNTER'     => true, 
    'SHOW_TOTAL_COUNTER'        => true, 
    'SHOW_PAGESIZE'             => true, 
	'TOTAL_ROWS_COUNT_HTML' => '<span class="main-grid-panel-content-title">Всего:</span> <span class="main-grid-panel-content-text">' . $nav->getRecordCount() . '</span>',
    'SHOW_ACTION_PANEL'         => true, 
    'ACTION_PANEL'              => [ 
        'GROUPS' => [ 
            'TYPE' => [ 
                'ITEMS' => [ 
					[
						'ID' => 'change_employee',
						'TYPE' => 'DROPDOWN',
						'ITEMS' => [
							['VALUE' => '', 'NAME' => '- Выбрать -'],
							['VALUE' => 'allow', 'NAME' => 'Активировать'],
							['VALUE' => 'forbid', 'NAME' => 'Деактивировать'],
						],
					],
                    $editButton, 
                    $removeButton,
					$forAllCheckbox
                ], 
            ] 
        ], 
    ], 
    'ALLOW_COLUMNS_SORT'        => true, 
    'ALLOW_COLUMNS_RESIZE'      => true, 
    'ALLOW_HORIZONTAL_SCROLL'   => true, 
    'ALLOW_SORT'                => true, 
    'ALLOW_PIN_HEADER'          => true, 
    'AJAX_OPTION_HISTORY'       => 'N' 
]);
?>

<script src="static/xlsx.full.min.js"></script>
<script type="text/javascript">
$(function(){
	var gridListId = '<?=$listId?>';
	var Grid = BX.Main.gridManager.getById(gridListId);
	Grid = Grid ? Grid.instance : null;
	if (Grid) {
		// var rowsCollectionWrapper = Grid.getRows();
		// var rowsList = rowsCollectionWrapper.getRows();
		// console.log(rowsList);
	    var reloadParams = {apply_filter: 'Y', clear_nav: 'N'};
        function makeStaticPage() {
            var pageNumber = $('.main-ui-pagination-active').text()
            var resPage = {};
            resPage[gridListId] = 'page-' + pageNumber;
            Grid.baseUrl = BX.Grid.Utils.addUrlParams(Grid.baseUrl, resPage);            
        }
		Grid.changeSelectedPrograms = function(id, dataItem) {
			if (id == 'change_employee_control') {
				// console.log(dataItem.VALUE);
            	makeStaticPage();
				var selectedRowsIdsList = [];
				$('input[name="ID[]"]:checked').each(function(index, element) {
					var rowId = $(element).val();
					selectedRowsIdsList.push(rowId);
				});
				var dataToOrm = { ACTION: 'bx.grid.'+dataItem.VALUE, ROWS: selectedRowsIdsList };
				$.post('crud.php', dataToOrm, function (data) {
					// console.log(data);
					Grid.reloadTable('POST', reloadParams);
				}, "json");			
			}
		}
		Grid.removeSelected = function() {
            makeStaticPage();
			var selectedRowsIdsList = [];
			$('input[name="ID[]"]:checked').each(function(index, element) {
				var rowId = $(element).val();
				selectedRowsIdsList.push(rowId);
			});
			// console.log(selectedRowsIdsList.join('|'));
			var dataToOrm = { ACTION: 'bx.grid.delete', ROWS: selectedRowsIdsList };
			$.post('crud.php', dataToOrm, function (data) {
				// console.log(data);
				Grid.reloadTable('POST', reloadParams);
			}, "json");
		}
        window.deleteThisEmployee = function(infoId) {
            makeStaticPage();
			var dataToOrm = { ACTION: 'bx.grid.delete', ROWS: [infoId] };
			$.post('crud.php', dataToOrm, function (data) {
				// console.log(data);
				Grid.reloadTable('POST', reloadParams);
			}, "json");
        }
		Grid.editSelectedSave = function() {
            makeStaticPage();
			var selectedRowsList = [];
			$('input[name="ID[]"]:checked').each(function(key, rowElement) {
				var rowId = $(rowElement).val();
				var rowArray = {};
				rowArray['ID'] = rowId;
				$('tr[data-id="'+rowId+'"]').find('input, div.main-ui-multi-select, div.main-grid-editor-dropdown').each(function(index, element) {
					var elemName = $(element).attr('name');
                    if (elemName == 'PROPERTY_REQUEST_STATUS_ENUM_ID') elemName = 'PROPERTY_REQUEST_STATUS_VALUE';
                    if (elemName == 'PROPERTY_CURRENT_RANK_NAME_ENUM_ID') elemName = 'PROPERTY_CURRENT_RANK_NAME_VALUE';
                    if (elemName == 'PROPERTY_NEXT_RANK_NAME_ENUM_ID') elemName = 'PROPERTY_NEXT_RANK_NAME_VALUE';
                    if (elemName == 'PROPERTY_MILITARY_RANK_ENUM_ID') elemName = 'PROPERTY_MILITARY_RANK_VALUE';
                    if (elemName == 'PROPERTY_POLICE_RANK_ENUM_ID') elemName = 'PROPERTY_POLICE_RANK_VALUE';
                    if (elemName == 'PROPERTY_JUSTICE_RANK_ENUM_ID') elemName = 'PROPERTY_JUSTICE_RANK_VALUE';
                    if (elemName == 'PROPERTY_PROSECUTORS_RANK_ENUM_ID') elemName = 'PROPERTY_PROSECUTORS_RANK_VALUE';
					var elemValue = $(element).val();
					if ($(element).attr('type') == 'checkbox') elemValue = $(element).prop('checked') ? 'Y': 'N';
					if ($(element).hasClass("main-grid-editor-dropdown")) elemValue = $(element).attr('data-value');
					if ($(element).hasClass("main-ui-multi-select")) {
						var arMultiselect = JSON.parse($(element).attr('data-value'));
						elemValue = [];
						$.each(arMultiselect, function(key1, arValue) {
							elemValue.push(arValue['VALUE']);
						});
						if (!elemValue[0]) elemValue = false;
					}
					if (elemName && (index != 0)) rowArray[elemName] = elemValue;
				});
				selectedRowsList.push(rowArray);
			});
			// console.log(selectedRowsList);
			var dataToOrm = { ACTION: 'bx.grid.update', ROWS: selectedRowsList };
			$.post('crud.php', dataToOrm, function (data) {
				// console.log(data);
				Grid.reloadTable('POST', reloadParams);
			}, "json");
		}
	}
});

BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (command, params) {    
    $.getJSON("?IFRAME=Y&EXCEL=DOWNLOAD", function(json) {
        console.log(json[0]);
    });
}));
var menu = [];
menu.push({
      text: "Импорт", // Название пункта
      title: "Загрузить данные из шаблона Excel", // Всплывающая подсказка
      href: 'import.php', // Ссылка
      className: 'menu-popup-excel', // Дополнительные классы
});
menu.push({
      text: "Отчёт", // Название пункта
      title: "Выгрузить данные из списка в Excel", // Всплывающая подсказка
      href: '#', // Ссылка
      className: 'menu-popup-excel', // Дополнительные классы
      onclick: function(e, item){
          BX.PreventDefault(e);   // Событие при клике на пункт
          // console.log(item);
          $.getJSON("?IFRAME=Y&EXCEL=DOWNLOAD", function(json) {
			// console.log(json);
            exportWorksheet(json[1]);
          });
          popupMenu.popupWindow.close(); // закрытие окна
      }
});
var params = {
      offsetLeft: 20,
      closeByEsc: true,
      angle: {
          position: 'top'
      },
      events: {
          onPopupClose : function(){
                //обработка событии при закрытии меню
          }
      }
}
var popupMenu = new BX.PopupMenuWindow(
      "myPopupForm",
      BX("menu-btn"),
      menu,
      params
);  

function exportWorksheet(jsonObject) {
  var myFile = "statement.xlsx";
  var worksheet = XLSX.utils.json_to_sheet(jsonObject);
  worksheet['!cols'] = [{width: 36}, {width: 15}, {width: 22}, {width: 52}, {width: 90}, {width: 75}, {width: 38}, {width: 32}, {width: 75}, {width: 60}];
  worksheet['!rows'] = [{hpx: 30}];
  var myWorkBook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(myWorkBook, worksheet, "Присвоение классных чинов");
  XLSX.writeFile(myWorkBook, myFile);
}

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>