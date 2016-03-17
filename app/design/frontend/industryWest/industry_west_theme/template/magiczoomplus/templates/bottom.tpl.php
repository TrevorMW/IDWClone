<?php

$m = intval(self::$options->getValue('selectors-margin'));
$wm = intval(self::$options->getValue('thumb-max-width')); ?>

<!-- Begin magiczoomplus -->
<div class="MagicToolboxContainer" >
    <?php echo $main?>

    <?php if( isset($message) ):?>
      <div class="MagicToolboxMessage"><?php echo $message ?></div>
    <?php endif?>

    <?php if(count($thumbs) > 1): $slider_attr = '' ; count( $thumbs ) >= 5 ? $slider_attr = 'data-product-thumbs="true" ' : ''; ?>

      <div id="MagicToolboxSelectors<?php echo $pid?>" class="MagicToolboxSelectorsContainer<?php echo $magicscroll;?>"  <?php echo $slider_attr; ?>>
          <?php echo join("\n\t",$thumbs)?>
      </div>
      <?php if(!empty($magicscroll)): ?>
        <script type="text/javascript">
          MagicScroll.extraOptions.MagicToolboxSelectors<?php echo $pid?> = MagicScroll.extraOptions.MagicToolboxSelectors<?php echo $pid?> || {};
          MagicScroll.extraOptions.MagicToolboxSelectors<?php echo $pid?>.direction = 'right';
          <?php if(self::$options->checkValue('width', 0)): ?>
          MagicScroll.extraOptions.MagicToolboxSelectors<?php echo $pid?>.width = <?php echo $wm?>;
          <?php endif?>
        </script>
      <?php endif?>

    <?php endif?>

    <?php if(isset($hotspots)):?>
      <div>
        <?php echo $hotspots?>
      </div>
    <?php endif?>
</div>

<!-- style="max-width: <?php echo $wm?>px"  -->
