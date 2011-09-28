{{= Dibasic.DPNavigation.widget() }}

<?if(!$Dibasic->tableExists):?>

	<?$Dibasic->addPlugin('CreateForm')?>
	
	Table does not exist.
	{{= Dibasic.DPCreateForm.widget() }}

<?elseif($Dibasic->tableNeedsModifications):?>

	<?$Dibasic->addPlugin('AlterForm')?>

	Table needs modifications.
	{{= Dibasic.DPAlterForm.widget() }}

<?else:?>

	<div>
	{{= Dibasic.DPAddForm.widget() }}
	{{= Dibasic.dataRenderer.sortWidget() }}
	{{= Dibasic.dataRenderer.filterWidget() }}
	{{= Dibasic.dataRenderer.searchWidget() }}
	</div>

	<div>
	{{= Dibasic.dataRenderer.widget() }}
	</div>

<?endif;?>