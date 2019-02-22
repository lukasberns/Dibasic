{{= Dibasic.DPNavigation.widget() }}

<?php if(!$Dibasic->tableExists):?>

	<?php$Dibasic->addPlugin('CreateForm')?>
	
	Table does not exist.
	{{= Dibasic.DPCreateForm.widget() }}

<?php elseif($Dibasic->tableNeedsModifications):?>

	<?php$Dibasic->addPlugin('AlterForm')?>

	Table needs modifications.
	{{= Dibasic.DPAlterForm.widget() }}

<?php else:?>

	<div>
	{{= Dibasic.DPAddForm.widget() }}
	{{= Dibasic.dataRenderer.sortWidget() }}
	{{= Dibasic.dataRenderer.filterWidget() }}
	{{= Dibasic.dataRenderer.searchWidget() }}
	</div>

	<div>
	{{= Dibasic.dataRenderer.widget() }}
	</div>

<?php endif;?>
