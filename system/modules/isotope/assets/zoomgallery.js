Isotope.zoomGallery = function(gallery, src, options)
{
    if (typeof gallery != 'undefined') gallery.destroy();

    return new ZoomIt(Object.merge(
    {
        'elems': '.zoomImage',
        'imgSrc': src,
        'multiplier': 1.4,
        'zoomPosition': 'right'
    }, options));
};


/**
 * Author: Constantin Boiangiu (constantin[at]php-help.ro)
 * Homepage: http://www.php-help.ro/php-tutorials/zoomit-javascript-image-zoom/
 * Mootools version: 1.3 (1.2 compatible)
 * Copyright © author
 * License: MIT (http://www.opensource.org/licenses/mit-license.php)
 */
var ZoomIt = new Class({
    Implements: [Events,Options],
    options:{
        container: null, // if left null, body will be scanned
        elems: '.zoomIt', // css class to scan for
        imgSrc: '', // path to big image
        mouseEvent: 'mouseenter', // click, mouseenter, mouseover
        multiplier: 1, // how much of the big image will be displayed
        zoomPosition: 'bottom', // left, right, top, bottom
        zoomDistance: 10, // distance of the zoomer from the trigger
        zoomClass: 'zoomIt_zoomed', // additional styling on zoom container
        zoomLoadingClass: 'zoomIt_loading', // css class to apply before the big image is fully loaded
        zoomAreaClass: 'zoomIt_area', // additional styling on zoomed area
        zoomAreaColor: '#FFF', // zoomed area bg color
        zoomAreaOpacity: .5, // zoomed area opacity
        zoomAreaMove: 'mousemove' // drag or mousemove
        /*onZoom: $empty*/
        /*onClose: $empty*/
    },
    initialize: function(options){
        this.setOptions(options);
        if( !this.options.elems ) return;
        this.elements = $(this.options.container||document.body).getElements(this.options.elems);
        this.zoomerVisible = false;
        this.current = -1;

        /* the container for the zoomed image */
        this.zoomer = new Element('div', {
            'id':'MooZoom_zoomer',
            'class':this.options.zoomClass,
            'styles':{
                'display':'block',
                'position':'absolute',
                'top':-1000,
                'overflow':'hidden'
            }
        }).inject(document.body);

        /* all elements in page that have zoom */
        this.elements.each(function(el, i){
            var params = {};
            params.bigImgURL = this.options.imgSrc; //el.getProperty('href');
            //el.setProperty('href', '#');
            var imgSize = el.getElement('img').getSize();
            params.imgSize = imgSize;
            // zoomer position
            var elPos = el.getPosition(),
                elSize = el.getSize();
            params.position = {'x':elPos.x, 'y':elPos.y};
            switch( this.options.zoomPosition ){
                case 'right':
                default:
                    elPos.x += elSize.x + this.options.zoomDistance;
                break;
                case 'left':
                    elPos.x -= imgSize.x * ( this.options.multiplier || 1 ) + this.options.zoomDistance;
                break;
                case 'bottom':
                    elPos.y += elSize.y + this.options.zoomDistance;
                break;
                case 'top':
                    elPos.y -= imgSize.y * ( this.options.multiplier || 1 ) + this.options.zoomDistance;
                break;
            }
            // store position on element
            params.zoomPosition = elPos;
            var dragged = new Element('div',{
                'class': this.options.zoomAreaClass,
                styles:{
                    'background': this.options.zoomAreaColor || '#999',
                    'opacity': this.options.zoomAreaOpacity || .7,
                    'display':'none',
                    'position':'absolute',
                    'top':0,
                    'left':0,
                    'cursor':'move'
                }
            }).inject(el.getParent());

            params.dragged = dragged;
            el.store('params', params);
            // add mouse event on element
            el.addEvent(this.options.mouseEvent||'mouseenter', function(event){
                event.preventDefault();
                this.startZoom(i);
            }.bind(this))
            el.getParent().addEvent('mouseleave', this.closeZoom.bind(this));
        }.bind(this))
    },

    startZoom: function(index){
        if( this.zoomerVisible ){
            return;
        }
        this.zoomerVisible = true;
        this.current = index;
        var e = this.elements[index],
            p = e.retrieve('params'),
            imgSize = p.imgSize,
            img = p.bigImgURL,
            drag = p.dragged,
            dragParams = e.retrieve('dragParams'),
            zoomerWidth = 0,
            zoomerHeight = 0;

        // if multiplier is larger than image full size, show full sized image with no drag
        if( dragParams && dragParams.fullImageSize ){
            zoomerWidth = dragParams.fullImageSize.x;
            zoomerHeight = dragParams.fullImageSize.y;
        }else{
            zoomerWidth = imgSize.x * ( this.options.multiplier || 1 );
            zoomerHeight = imgSize.y * ( this.options.multiplier || 1 );
        }
        // set zoomer size
        this.zoomer.empty().setStyles({
            'width':zoomerWidth,
            'height':zoomerHeight
        }).setPosition(p.zoomPosition);

        // if drag params are set, big image was loaded so just set some styles
        if( dragParams ){
            dragParams.bigImg.inject(this.zoomer);
            drag.setStyles({'display':'block', 'width':dragParams.dragW, 'height':dragParams.dragH});
            this.fireEvent('onZoom', e);
            return;
        }
        this.zoomer.addClass(this.options.zoomLoadingClass);
        // on first run, load the big image and set drag
        var bigImg = Asset.image(img,{
            onLoad: function(e){
                this.zoomer.removeClass(this.options.zoomLoadingClass);
                bigImg.setStyles({'position':'absolute', 'top':0, 'left':0}).inject(this.zoomer);

                var s = bigImg.getSize(),
                    ratioX = s.x / imgSize.x,
                    ratioY = s.y / imgSize.y,
                    dragW = imgSize.x/ratioX*(this.options.multiplier||1),
                    dragH = imgSize.y/ratioY*(this.options.multiplier||1);

                if( this.options.multiplier > ratioX && this.options.multiplier > ratioY ){
                    this.zoomer.setStyles({
                        'width':s.x,
                        'height':s.y
                    })
                    var params = {};
                    params.bigImg = bigImg;
                    params.fullImageSize = s;
                    this.elements[index].store('dragParams', params);
                    return;
                }

                drag.setStyles({'display':'block', 'width':dragW, 'height':dragH});
                var initPosition = drag.getPosition(drag.getParent());
                bigImg.setStyles({'top': -(initPosition.y*ratioY), 'left': -(initPosition.x*ratioX)});

                // set the drag params. this prevents the script to load the big image again
                var params = {};
                params.bigImg = bigImg;
                params.dragW = dragW;
                params.dragH = dragH;
                this.elements[index].store('dragParams', params);

                if( this.options.zoomAreaMove == 'mousemove' ){
                    // mouse move on image
                    this.elements[index].addEvent('mousemove', function(event){
                        var mPosX = event.page.x - p.position.x - dragW/2,
                            mPosY = event.page.y - p.position.y - dragH/2;

                        // horizontal right limit
                        if( event.page.x > ( p.position.x + p.imgSize.x - dragW/2 )){
                            mPosX = p.imgSize.x - dragW;
                        }
                        // vertical bottom limit
                        if( event.page.y > ( p.position.y + p.imgSize.y - dragH/2 )){
                            mPosY = p.imgSize.y - dragH;
                        }
                        drag.setPosition({'x':mPosX, 'y':mPosY});
                    }.bind(this))
                    // mousemove n dragged zoom area
                    drag.addEvent('mousemove', function(event){
                        var mX = event.page.x - p.position.x - dragW/2,
                            mY = event.page.y - p.position.y - dragH/2;
                        // horizontal left limit
                        if( event.page.x < p.position.x + dragW/2 ){
                            mX = 0;
                        }
                        // vertical top limit
                        if( event.page.y < p.position.y + dragH/2 ){
                            mY = 0;
                        }
                        // horizontal right limit
                        if( event.page.x > ( p.position.x + p.imgSize.x - dragW/2 )){
                            mX = p.imgSize.x - dragW;
                        }
                        // vertical bottom limit
                        if( event.page.y > ( p.position.y + p.imgSize.y - dragH/2 )){
                            mY = p.imgSize.y - dragH;
                        }
                        // move zoomed area
                        drag.setPosition({'x':mX, 'y':mY});

                        var pos = drag.getPosition(drag.getParent()),
                            left = -(pos.x*ratioX),
                            top = -(pos.y*ratioY);
                        bigImg.setPosition({'x':left, 'y':top});
                    })
                }else{
                    // start drag
                    new Drag(drag,{
                        modifiers: {x:'left',y:'top'},
                        grid:1,
                        limit: {x:[0,(imgSize.x - dragW)], y:[0, (imgSize.y-dragH)]},
                        onDrag: function(draggedEl){
                            var pos = draggedEl.getPosition(draggedEl.getParent()),
                                left = -(pos.x*ratioX),
                                top = -(pos.y*ratioY);
                            bigImg.setPosition({'x':left, 'y':top});
                        }.bind(this)
                    });
                }

                this.fireEvent('onZoom', e);
            }.bind(this)
        });
    },

    closeZoom: function(event){
        if( this.current == -1 ) return;

        var e = this.elements[this.current],
            drag = e.retrieve('params').dragged.setStyle('display', 'none');

        this.zoomer.setStyles({'top':-1000}).empty();
        this.current = -1;
        this.zoomerVisible = false;
        this.fireEvent('onClose', e);
    },

    destroy: function(){
        this.elements.each(function(e){
            var p = e.retrieve('params');
            e.setProperty('href', p.bigImgURL);
            p.dragged.dispose();
            e.eliminate('params').eliminate('dragParams');
            e.removeEvents();
            e.getParent().removeEvents();
        })
        this.zoomer.dispose();
    }
});