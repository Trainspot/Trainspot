(function($) {

    $.fn.µSlide = function(p_options) {

        // to escape some inheritance problem
        var $that       = $(this),
            that        = this,
        // id of the current slide
            slideId     = false,
        // iscroll
            iScrollInst = false,
        // detects to know if we use iScroll or scrollTo
            hasTouch    =  'ontouchstart' in window;
        // timestamp to prevent double click on ipad
            lastSlideTo = 0,
        // every slide
            $slides     = $();
                    
        
        /**
         * Default options
         * @public
         * @type   object
         */
        that.options = {
              
              /**
               * If we want, we could use a class (gived in parameter) 
               * to know  witch child to complete in the contener
               * @type   string
               */
              child_class     : null,
              
              /**
               * First slide to show
               * @type integer
               */
              startSlide      : false,
              
              /**
               * Wrapper selector
               * @type string
               */
              wrapper         : ".js-wrapper",
                                                  
              /**
               * True if we wanna resize the contener following the slide
               * @type boolean
               */
              resize           : true,

              /**
               * 
               *
               */
              leftOffset: 0,

              /**
               * 
               *
               */
              slideWidth: false
        };
        
        
        
        /**
         * thatChange the slide according to the direction or an id
         * @param  string or integer
         * @public
         */
        this.slideTo = function(direction, duration) {

            var n_time = new Date().getTime(); 

            if(n_time - lastSlideTo > 500) {

                switch(true) {

                    case isNaN(direction):
                        // if we specify a direction to change 
                        // and the if the last slide is older than 100 millisecond
                        lastSlideTo = n_time;                              

                        switch(direction) {

                            case "next":
                                slideId -= (slideId < $slides.length - 1) ? -1 : 0;
                                break;

                            case "previous":
                                slideId -= (slideId > 0) ? 1 : 0;
                                break;

                        }                              
                        loadSlide(duration);
                        break;


                    default:                                      
                        lastSlideTo = n_time ;  
                        slideId = direction >= 0 && direction < $slides.length ? direction : slideId;                                                            

                        loadSlide(duration);
                        break;
                }
            }
        };
        
        
        /**
         * that  load the current slide following  the local variable "slideId"
         * @private
         */
        var loadSlide = function(duration) { 

            duration = typeof duration !== "number" ? 300 : duration;                        

            // if we use iScroll
            if(hasTouch) {
                
                iScrollInst.scrollToPage( slideId, 0, duration);   

            // else we use jQuery.scrollTo        
            } else $that.scrollTo( that.get(slideId), {                 
                duration: duration,
                onAfter : slideChange,
                offset: {
                    left: that.options.leftOffset
                }
            });
        
        };
        
        /**
         * Change the slide id when the scroll stop
         * @private
         */
        var slideChange = function() {

            if(hasTouch) slideId = iScrollInst.currPageX;

            // change the active slide
            $slides.filter(".js-active").removeClass("js-active");
            $slides.eq(slideId).addClass("js-active");

            afterSlide(slideId);            
        };
        

        /**
         * Binds default events on some elements
         * @private
         */
        var bindEvent = function() {

            if(!hasTouch) {
                $slides.unbind("click").on("click", function(event) {
                    if( !$(this).hasClass(".js-active") ) {
                         that.slideTo( $(this).index() );
                    }
                });
            }
        };

        
        /**
         * Function triggered before each sliding
         * @private
         */
        var afterSlide = function(n_slide) {  
              
              // sliding may have change the size au content
              that.refreshHeight();              
              // triggers the listeners on the after-slide event
              $that.trigger("after-slide", [n_slide, $slides.eq(n_slide)[0] ] );
        };
        
        
        /**
         * Refresh the slide height following the current slide
         * @private
         */
        that.refreshHeight = function() {
              
            // if we wanna resize the contener following the current slide
            if(!! that.options.resize && that.get(slideId).outerHeight() > 0) {
                    $that.css("height", that.get(slideId).outerHeight() );
            }
        };
        
        /**
         * Get a slide
         * @param number n_slide Slide number
         * @public
         */
        that.get = function(n_slide) {            
            // current slide                         
            return typeof n_slide !== "undefined" ? $slides.eq(n_slide) : $slides;            
        };

        /**
         *
         *
         */
         that.append = function(el) {
            var currentSlideId = slideId;
            $(".js-wrapper", that).append(el);
            that.init();
        };
        

        /**
         *
         *
         */
         that.prepend = function(el) {

            $(".js-wrapper", that).prepend(el);
            that.init();
            ++slideId;
            loadSlide(0);
        };
        
        
        /**
         * that  Initialisation method
         * @public
         */
        that.init = function(p_options) {                

            if(p_options) {
                // User defined optionss
                for (i in p_options) {
                      that.options[i] = p_options[i];
                }
            }

            // Every slides
            $slides = $that.find(that.options["child_class"]),
            // Wrapper width
            width = 0;

            $slides.each(function(i, slide) {                  
                width += that.options.slideWidth || $(slide).outerWidth();
            });

            if(hasTouch) width += -2*that.options.leftOffset;

            // wrapper width and style
            $that.find(".js-wrapper").css({
                width:width,
                height:1,
                paddingLeft:  hasTouch ? 0 : -1*that.options.leftOffset,
                paddingRight: hasTouch ? 0 : -1*that.options.leftOffset
            });                     

            // first slide
            $slides.removeClass("js-first js-last").eq(0).addClass("js-first");
            // last slide
            var n = $slides.length;
            $slides.eq(n-1).addClass("js-last");

                
            // iScroll on the slide
            if(hasTouch && !iScrollInst) {    


                // only if we are on webkit navigator
                iScrollInst = new iScroll( $that.attr("id"), {                    
                      // to jump between each slide
                      snap:'.js-card',
                      // hides horizontal scrollBar
                      hScrollbar:false, 
                      // allows horizontal movment
                      hScroll:true, 
                      // allows only one direction movment in the same time
                      lockDirection: true,
                      // hides vertical scrollBar
                      vScrollbar: false,
                      // forbids vertical movment
                      vScroll: false,
                      // turn of movment elasticity 
                      momentum:false,
                      // enables hozizontal preventDefault event
                      hPreventDefault: true,
                      // disables vertical preventDefault event
                      vPreventDefault: false,
                      // calls function "slideChange" when the scroll stops
                      onScrollEnd: slideChange,
                      // no bounce effect
                      bounce:true,
                      // disable scroll
                      wheelAction: 'none',
                });    


            } else if(hasTouch) {                
                // Just refresh
                iScrollInst.refresh();
            };


            if(hasTouch) {
                
                $that.find(".js-wrapper").css({                    
                    paddingLeft:  -1*that.options.leftOffset
                }); 
            }

          
            if( ! $that.data("µSlide") ) {
              
                // slide to the first slide                  
                that.slideTo(  slideId || that.options.startSlide  || ($slides.filter(".js-active").index() > -1 ? $slides.filter(".js-active").index() : 0 ) );

                $that.data("µSlide", {
                    append:  that.append,
                    prepend: that.prepend,
                    get:     that.get,
                    slideTo: that.slideTo,
                    init:    that.init
                });
            
            }

            // Binds default events on some elements
            bindEvent();


            return that;                 

        };

        return that.init(p_options);
    };

})(jQuery);