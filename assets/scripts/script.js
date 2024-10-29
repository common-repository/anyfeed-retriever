/**
 * Created by anushkar on 2/1/19.
 * v 1.0.0
 */
(function ($) {

    $(document).ready(function(){


      //  var _elem0 = $('.feed-block:first-of-type .feed-items');

        var _elem0 = $('.feed-block .feed-items');
        _elem0.each(function(){
            loadLazyFeedinit(this);
        });



           // $(window).scroll(function() {
            //    var _elem = $('.feed-items');
            //    _elem.each(loadLazyFeed);

          //  });

        function htmlDecode(input){
            var e = document.createElement('div');
            e.innerHTML = input;
            return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
        }

        function loadLazyFeedinit(_elem0){

            //check if your div is visible to user
            // CODE ONLY CHECKS VISIBILITY FROM TOP OF THE PAGE

                if(!$(this).attr('loaded')) {

                    var _elem = _elem0;

                    var jqxhr = $.ajax({
                        url: anyfeed_var.ajaxurl,
                        type: 'post',
                        data: {
                            'action':'load_anyfeeds',
                            'feedurl': $(_elem).data('feedurl')
                        }})
                        .done(function(result) {
                            console.log( "success" );
                            $(_elem).attr('loaded', true);
                            $(_elem).empty();
                            _feeditems = JSON.parse(result);
                            $.each(_feeditems, function(){
                               // this.description = htmlDecode(this.description);
                                this.description = $('<div>').append(this.description).html();
                                __feeditemshtml = Mustache.render(anyfeed_var.feeditem,this);
                                $_item = $(_.unescape(__feeditemshtml));
                               // if(this.extra != undefined && this.extra.contentType == 'html')
                                    $_item.find('.item-desc').replaceWith($('<div class="item-desc">').append(this.description));
                                $(_elem).append($_item);
                            });
                            sortfeeditems();
                            moveCategory();
                        })
                        .fail(function(error) {
                            console.log( "error" );
                        })
                        .always(function() {
                            console.log( "complete" );
                        });

                }

        }

            function sortfeeditems(){
                var $wrapper = jQuery('.anyfeed-container');
                undefined
                $wrapper.find('.feed-item').sort(function (a, b) {
                    return b.getAttribute('data-timestamp') - a.getAttribute('data-timestamp');
                }).detach().appendTo( $wrapper );
            }

            function moveCategory(){
                $('.anyfeed-container .feed-category > a').each(function(){
                    if($('.anyfeed-cta-block ul.anyfeed-categories li a[href="'+$(this).attr('href')+'"]').length <= 0)
                        $('.anyfeed-cta-block ul.anyfeed-categories').append($('<li>').append($(this)));
                });
            }

            function calcElapsedTime($datetime){

            }

    })
})(jQuery)