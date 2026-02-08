jQuery(document).ready(function($){
  $(document).on('click', '.imgbox-load-more', function(){
    var btn = $(this);
    var postID = btn.data('post');

    $.post(imgblm_ajax.ajax_url, {
      action: 'imgblm_load_more',
      post_id: postID
    }, function(response){
      btn.before(response);
      btn.remove();
    });
  });
});
