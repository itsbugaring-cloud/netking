<?php if($paginator->hasPages()): ?>
<nav class="nk-pager-wrap">
    <div class="nk-pager-info">
        Menampilkan <strong><?php echo e($paginator->firstItem()); ?></strong>–<strong><?php echo e($paginator->lastItem()); ?></strong>
        dari <strong><?php echo e($paginator->total()); ?></strong> data
    </div>
    <div class="nk-pager">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="nk-pager-nav disabled">‹ Sebelumnya</span>
        <?php else: ?>
            <a class="nk-pager-nav" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">‹ Sebelumnya</a>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="nk-pager-num disabled"><?php echo e($element); ?></span>
            <?php endif; ?>
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="nk-pager-num active"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a class="nk-pager-num" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a class="nk-pager-nav" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Selanjutnya ›</a>
        <?php else: ?>
            <span class="nk-pager-nav disabled">Selanjutnya ›</span>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
<?php /**PATH /var/www/netking.id/resources/views/vendor/pagination/netking.blade.php ENDPATH**/ ?>