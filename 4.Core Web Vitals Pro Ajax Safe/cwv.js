jQuery(function($){
  const btn = $('#cwv-load-more');
  if (!btn.length) return;

  btn.on('click', function(){
    const offset = parseInt(btn.attr('data-offset'), 10);
    btn.prop('disabled', true).text('Loading...');

    $.post(CWV.ajax, {
      action: 'cwv_load_more',
      nonce: CWV.nonce,
      post: CWV.post,
      offset: offset
    }, function(res){
      if (!res.success || !res.data.html) {
        btn.remove();
        return;
      }

      btn.before(res.data.html);
      const newOffset = offset + res.data.loaded;
      btn.attr('data-offset', newOffset);

      if (res.data.loaded < CWV.batch) {
        btn.remove();
      } else {
        btn.prop('disabled', false).text('Load more images');
      }
    });
  });
});