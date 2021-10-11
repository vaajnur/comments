<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
        Bitrix\Iblock,
        Bitrix\Main\Context,
        Bitrix\Main\Localization\Loc;
/**
 * class Comments
 *
 * it add and list comments for any iblock element
 */
class Comments extends CBitrixComponent
{
    const COMMENTS_IBLOCK_TYPE = 'service';
    const COMMENTS_IBLOCK_CODE = 'comments';
    private $commentsIblockID;
    private $commentsIblockName;
    private $emailPropertyID;
    private $emailPropertyCODE;
    private $elementPropertyID;
    private $elementPropertyCODE;
    private $formFields;


    public function __construct($component = null)
    {
        $this->commentsIblockName = Loc::getMessage('COMMENTS_IBLOCK_NAME');
        $this->emailPropertyCODE = 'EMAIL';
        $this->elementPropertyCODE = 'ELEMENT_ID';
        $this->formFields = array('name', 'email', 'comment');
        parent::__construct($component);
    }

    /**
     * Check Required Modules
     *
     * @throws Exception
     */
    protected function checkModules()
    {
        if(!Loader::includeModule('iblock'))
        {
            return false;
        }

        return true;
    }

    /**
     * [проверка на существования типа инфоблока для комментов]
     * @return [type] [description]
     */
    protected function checkCommentsIblockType()
    {
        return CIBlockType::GetByID(self::COMMENTS_IBLOCK_TYPE)->GetNext();
    }

    /**
     * [проверка на существования самого инфоблока для комментов]
     * @return [type] [description]
     */
    protected function checkCommentsIblock()
    {
        $res = CIBlock::GetList(
            Array(), 
            Array(
                'TYPE'=> self::COMMENTS_IBLOCK_TYPE, 
                'SITE_ID'=>SITE_ID, 
                'ACTIVE'=>'Y', 
                "CNT_ACTIVE"=>"Y", 
                "CODE"=> self::COMMENTS_IBLOCK_CODE
            ), true
        );
        if($ar_res = $res->GetNext())
        {
            $this->commentsIblockID = $ar_res['ID'];
            return $ar_res;
        }
        else
        {
            return false;
        }

    }

    /**
     * [создаем отдельный тип инфоблока для комментов]
     * @return [type] [description]
     */
    protected function createCommentsIblockType()
    {
            $arFields = Array();
            $arFields["LANG"] = Array();
            $arFields['ID'] = self::COMMENTS_IBLOCK_TYPE;
            $arIBTLang = Array();
            $l = \CLanguage::GetList($lby="sort", $lorder="asc");
            while($ar = $l->GetNext())
                $arIBTLang[]=$ar;

            $LANG_FIELDS = [
                'ru' => ['NAME' => self::COMMENTS_IBLOCK_TYPE],
                'en' => ['NAME' => self::COMMENTS_IBLOCK_TYPE],
            ];
            foreach($arIBTLang as $ar)
                $arFields["LANG"][$ar["LID"]] = $LANG_FIELDS[$ar["LID"]];

            $obBlocktype = new \CIBlockType;
            $ID = $obBlocktype->Add($arFields);
            if($ID == false){
                $this->arResult["ERRORS"]['ib_type'][] = 'Iblock Type not added.';
            }

    }

    /**
     * [создаем отдельный инфоблок для комментов]
     * @return [type] [description]
     */
    protected function createCommentsIblock()
    {
        $ib = new \CIBlock;
        $arFields = Array(
          "ACTIVE" => 'Y',
          "NAME" => $this->commentsIblockName,
          "CODE" => self::COMMENTS_IBLOCK_CODE,
          "IBLOCK_TYPE_ID" => self::COMMENTS_IBLOCK_TYPE,
          "SITE_ID" => SITE_ID,
          "GROUP_ID" => Array("2"=>"R")
          );
          $ID = $ib->Add($arFields);
          if($ID<=0){
            $this->arResult["ERRORS"]['iblock_create'][] = $ib->LAST_ERROR;
          }
          else
          {
            $this->commentsIblockID = $ID;
             $arFields = Array(
                    "NAME" => "Email",
                    "ACTIVE" => "Y",
                    "SORT" => "600",
                    "CODE" => $this->emailPropertyCODE,
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $ID,
                );
             $arFields2 = Array(
                    "NAME" => "ID элемента",
                    "ACTIVE" => "Y",
                    "SORT" => "600",
                    "CODE" => $this->elementPropertyCODE,
                    "PROPERTY_TYPE" => "E",
                    "IBLOCK_ID" => $ID,
                );
                // Email
                if($ID2 = $this->addField($arFields))
                    $this->emailPropertyID = $ID2;
                else
                    $this->arResult["ERRORS"]['ib_property'][] = 'Iblock property Email not added.'; 
                // Element ID
                if($ID3 = $this->addField($arFields2))
                    $this->elementPropertyID = $ID3;
                else
                    $this->arResult["ERRORS"]['ib_property'][] = 'Iblock property Element ID not added.'; 
                if($ID2 == false || $ID3 == false)
                    \CIBlockType::Delete(self::COMMENTS_IBLOCK_TYPE);
          }
        return true;
    }

    /**
     * [добавляю свойства для инфоблока комментов]
     * @param [type] $arFields [description]
     */
    public function addField($arFields){
          $propertyObj = new \CIBlockProperty;
          $PropID = $propertyObj->Add($arFields);
          if($PropID == false){
            $this->arResult["ERRORS"]['ib_property'][] = $propertyObj->LAST_ERROR;
          }
          return $PropID;
    }

    /**
     * [проверка полей комментария перед отправкой]
     * @param  [type] $postValues [description]
     * @return [type]             [description]
     */
    public function validateFields($postValues)
    {
        $not_empty = array_filter($postValues, function($item){
            return $item == '';
        });
        return empty($not_empty);
    }    

    /**
     * [добавление комментария]
     * @param [type] $postValues [description]
     */
    public function addComment($postValues)
    {
        $el = new CIBlockElement;
        $PROP = array();
        // email
        $PROP[$this->emailPropertyCODE] = $postValues['email'];
        // elem id
        $PROP[$this->elementPropertyCODE] = $this->arParams['ELEMENT_ID'];
        $fields = array(
            'PROPERTY_VALUES' => $PROP,
            'NAME' => $postValues['name'],
            'DETAIL_TEXT' => $postValues['comment'],
            'IBLOCK_ID' => $this->commentsIblockID
        );
        if(!$el->Add($fields))
            $this->arResult["ERRORS"]['comment'][] = 'Comment not added. ' . $el->LAST_ERROR;
        else
            $this->arResult["MESSAGE"] = 'Success! Comment added!';            
    }

    /**
     * [получение списка добавленных коментов]
     * @param  string $element_id [по его id]
     * @return [type]             [description]
     */
    public function getElementComments(string $element_id)
    {
        if($this->commentsIblockID == false)
            return false;
        $comments = [];
        $res = CIBlockElement::getList(
                ['SORT'], 
                [ '=PROPERTY_ELEMENT_ID' => $element_id, 'ACTIVE' => 'Y', 'IBLOCK_ID' => $this->commentsIblockID ], 
                false, false, 
                ['NAME', 'ID', 'DETAIL_TEXT', 'IBLOCK_ID', 'PROPERTY_EMAIL']
            );
        while($ob = $res->getnext())
            $comments[] = $ob;
        return $comments;
    }

    /**
     * [executeComponent description]
     * @return [type] [description]
     */
     public function executeComponent()
    {
        $this->arResult["ERRORS"] = array();
        if(!$this->checkModules())
        {
            return;
        }

        if(!$this->checkCommentsIblockType())
        {
            $this->createCommentsIblockType();
        }

        if(!$this->checkCommentsIblock())
        {
            $this->createCommentsIblock();
        }

        // add comment
        $request = Context::getCurrent()->getRequest();
        $postValues = $request->getPostList()->toArray();

        if($postValues['send'] == true && check_bitrix_sessid()){
            if($this->validateFields($postValues)){
                $this->addComment($postValues);
            }else{
                $this->arResult["ERRORS"]['form'][] = 'Не заполнены все поля!';
            }
        }

        // добавленные комментарии
        if($this->startResultCache())
        {
            $this->arResult['COMMENTS'] = $this->getElementComments($this->arParams['ELEMENT_ID']);
            $this->includeComponentTemplate();
        }
        return $this->arResult;
    }

}
?>