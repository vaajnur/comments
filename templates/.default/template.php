<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc,
 		Bitrix\Main\Page\Asset;

$this->setFrameMode(false);
 Asset::getInstance()->addString('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">'); 

 if (!empty($arResult['COMMENTS'])) {?>
	<div class="container mt-5">
	    <div class="d-flex justify-content-center row">
	        <div class="col-md-12">
	            <div class="p-3 bg-white rounded">
	                <h6>Reviews[8]</h6>
					<? foreach ($arResult['COMMENTS'] as $key => $value) {?>
		                <div class="review mt-4">
		                    <div class="d-flex flex-row comment-user"><img class="rounded" src="" width="40">
		                        <div class="ml-2">
		                            <div class="d-flex flex-row align-items-center"><span class="name font-weight-bold"><?=$value['NAME'];?></span><span class="dot"></span><span class="date"><?=$value['EMAIL'];?></span></div>
		                            <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
		                        </div>
		                    </div>
		                    <div class="mt-2">
		                        <p class="comment-text"><?=$value['DETAIL_TEXT'];?></p>
		                    </div>
		                </div>
					<?} ?>
	            </div>
	        </div>
	    </div>
	</div>
<?} ?>

<?
if (!empty($arResult["ERRORS"])):?>
	<?
	foreach ($arResult["ERRORS"] as $key => $err) {
		ShowError(implode("<br />",  $err) );
	}
	?>
<?endif;
if (strlen($arResult["MESSAGE"]) > 0):?>
	<?ShowNote($arResult["MESSAGE"])?>
<?endif?>
<br>
<form name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
	  <?=bitrix_sessid_post()?>
	  <div class="form-group">
	    <label for="exampleInputPassword1"><? echo Loc::getMessage('FIO'); ?></label>
	    <input name="name" type="name" class="form-control" id="fio" placeholder="ФИО">
	  </div>
	  <div class="form-group">
	    <label for="exampleInputEmail1"><? echo Loc::getMessage('EMAIL'); ?></label>
	    <input name="email" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
	  </div>
	  <div class="form-group">
	    <label class="exampleFormControlTextarea1" for="exampleCheck1"><? echo Loc::getMessage('COMMENT'); ?></label>
	    <textarea  class="form-control"  name="comment" id="" cols="30" rows="10"></textarea>
	  </div>
	  <button name="send" type="submit" value="1" class="btn btn-primary"><? echo Loc::getMessage('SUBMIT'); ?></button>

</form>
<br>