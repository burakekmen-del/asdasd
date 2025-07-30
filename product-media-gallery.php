<div class="product-media-gallery" style="width:100%;max-width:380px;margin:0 auto;">
  <div class="product-media-main" style="position:relative;width:100%;height:340px;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:18px;background:#222;">
    <?php foreach($mediaList as $mi=>$media): ?>
      <?php if($media['type'] == 'video'): ?>
        <video 
          src="<?= htmlspecialchars($media['src']) ?>" 
          controls
          preload="none"
          poster="<?= htmlspecialchars($media['thumb'] ?? '') ?>"
          style="display:<?= $mi==0?'block':'none' ?>;width:100%;height:340px;object-fit:cover;border-radius:18px;cursor:pointer;background:#111;"
          onclick="openMediaModal('video','<?= htmlspecialchars($media['src']) ?>')"
        ></video>
        <?php if(!empty($media['caption'])): ?>
          <div class="media-caption" style="position:absolute;bottom:12px;left:18px;right:18px;background:rgba(20,20,20,0.76);color:#fff;padding:8px 13px;border-radius:8px;font-size:.98em;"><?= htmlspecialchars($media['caption']) ?></div>
        <?php endif; ?>
      <?php else: ?>
        <img 
          src="<?= htmlspecialchars($media['src']) ?>" 
          alt="<?= htmlspecialchars($media['caption'] ?? $urun['name']) ?>"
          loading="lazy"
          style="display:<?= $mi==0?'block':'none' ?>;width:100%;height:340px;object-fit:cover;border-radius:18px;cursor:zoom-in;"
          onclick="openMediaModal('img','<?= htmlspecialchars($media['src']) ?>')"
        />
        <?php if(!empty($media['caption'])): ?>
          <div class="media-caption" style="position:absolute;bottom:12px;left:18px;right:18px;background:rgba(20,20,20,0.76);color:#fff;padding:8px 13px;border-radius:8px;font-size:.98em;"><?= htmlspecialchars($media['caption']) ?></div>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if($discountPercent): ?>
      <span style="position:absolute;top:13px;left:13px;background:#00b894;color:#fff;padding:6px 14px 5px 14px;border-radius:8px;font-weight:700;font-size:1.04em;">-%<?= $discountPercent ?></span>
    <?php endif; ?>
    <!-- Galeri sola/sağa oklar -->
    <button type="button" class="media-thumb-arrow media-thumb-arrow-left" onclick="moveMediaThumb(-1)" style="position:absolute;left:0;top:50%;transform:translateY(-50%);background:#fff;border:none;width:32px;height:44px;border-radius:0 22px 22px 0;box-shadow:0 1px 7px #bbb;z-index:3;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="fa fa-chevron-left"></i></button>
    <button type="button" class="media-thumb-arrow media-thumb-arrow-right" onclick="moveMediaThumb(1)" style="position:absolute;right:0;top:50%;transform:translateY(-50%);background:#fff;border:none;width:32px;height:44px;border-radius:22px 0 0 22px;box-shadow:0 1px 7px #bbb;z-index:3;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="fa fa-chevron-right"></i></button>
  </div>
  <div class="product-media-thumbs-scroll" id="mediaThumbsScroll" style="margin-top:12px;overflow-x:auto;white-space:nowrap;width:100%;display:flex;gap:8px;align-items:center;justify-content:center;padding-bottom:2px;">
    <?php foreach($mediaList as $mi=>$media): ?>
      <div class="thumb-box" style="display:inline-block;">
        <?php if($media['type'] == 'img'): ?>
          <img 
            src="<?= htmlspecialchars($media['src']) ?>" 
            alt="<?= htmlspecialchars($media['caption'] ?? $urun['name']) ?>"
            class="product-thumb"
            style="width:46px;height:46px;border-radius:50%;object-fit:cover;box-shadow:0 1px 4px #e2e2f5;cursor:pointer;border:2.5px solid <?= $mi==0?'#6c5ce7':'#ececec' ?>;background:#fff;"
            onclick="setMediaSlide(<?= $mi ?>)">
        <?php else: ?>
          <span class="product-thumb product-thumb-video" style="width:46px;height:46px;border-radius:50%;background:#e6e8ff;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2.5px solid <?= $mi==0?'#6c5ce7':'#ececec' ?>;" onclick="setMediaSlide(<?= $mi ?>)">
            <i style="font-size:1.3em;color:#6c5ce7;" class="fa fa-play"></i>
          </span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
let currentMediaIndex = 0;
function setMediaSlide(idx) {
    currentMediaIndex = idx;
    const main = document.querySelector('.product-media-main');
    if (!main) return;
    let i = 0;
    Array.from(main.children).forEach(el => {
        if(el.tagName === 'IMG' || el.tagName === 'VIDEO')
            el.style.display = (i++ === idx) ? 'block' : 'none';
    });
    // thumb border
    document.querySelectorAll('.product-thumb').forEach((thumb, i) =>
        thumb.style.borderColor = (i === idx) ? '#6c5ce7' : '#ececec'
    );
}
function moveMediaThumb(dir) {
    const total = <?= count($mediaList) ?>;
    let next = currentMediaIndex + dir;
    if(next < 0) next = total-1;
    if(next >= total) next = 0;
    setMediaSlide(next);
    // thumb kaydır
    const scrollArea = document.getElementById('mediaThumbsScroll');
    const thumbs = scrollArea.querySelectorAll('.product-thumb, .product-thumb-video');
    if(thumbs[next]) {
        thumbs[next].scrollIntoView({behavior:'smooth',inline:'center'});
    }
}
</script>