/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id: checker_input_form.tmpl.php 463 2011-01-27 20:39:26Z cindy $

/*global window, jQuery*/

var AChecker = AChecker || {};

(function ($) {

    /**
     * global string function of trim()
     */
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, "");
    };

    /**
     * Open up a 1280x600 popup window
     */
    AChecker.popup = function (url) {
        var newwindow = window.open(url, 'popup', 'height=600, width=1280, scrollbars=yes, resizable=yes');
        if (window.focus) {newwindow.focus();}
    };

    /**
     * Toggle the collapse/expand images, alt texts and titles associated with the link
     * @param objId
     */
    AChecker.toggleDiv = function (objId, toggleImgId) {
        var toc = $("#" + objId);
        if (!toc) {
            return;
        }

        var toggleImg = $("#" + toggleImgId);
        if (toc.is(":visible")) {
            toggleImg.attr("src", "images/arrow-closed.png");
            toggleImg.attr("alt", "Expand");
            toggleImg.attr("title", "Expand");
        } else {
            toggleImg.attr("src", "images/arrow-open.png");
            toggleImg.attr("alt", "Collapse");
            toggleImg.attr("title", "Collapse");
        }

        toc.slideToggle();
    };

    /**
     * Display and activate the selected tab div
     * @param divId: the id of the tab div to display
     *        divMapping: The mapping between the tab IDs and corresponding menu IDs.
     * @returns return false if divId does not exist. Otherwise, show divId and hide other divs in the array allDivIds 
     */
    AChecker.showDivOutof = function (divId, divMapping) {
        if (!$("#" + divId)) {
            return false;
        }

        for (var eachDivId in divMapping) {
            if (eachDivId === divId) {
                $("#" + divId).show();
                $("#" + divMapping[eachDivId].menuID).addClass("active");
            } else {
                $("#" + eachDivId).hide();
                $("#" + divMapping[eachDivId].menuID).removeClass("active");
            }
        }
    };

    /**
     * Covers the DIV (divID) with a dynamically-generated disabled look-and-feel div.
     * The disabled div has the same size of divID (the 1st parameter) and is appended
     * onto the parentDivID (the 2nd parameter).
     * and append it to the pa 
     * @param divID: the div to cover
     * @param parentDivID: the parent div of divID (1st parameter)
     */
	AChecker.disableDiv = function (divID, parentDivID) {
        var cDivs = [];

        var d = $("#" + parentDivID); // parent div to expand the disabled div
        var e = $("#" + divID);  // the dynamically generated disabled div

        var xPos = e.offsetLeft;
        var yPos = e.offsetTop;
        var oWidth = e.offsetWidth;    
        var oHeight = e.offsetHeight;
        cDivs[cDivs.length] = document.createElement("DIV");
        cDivs[cDivs.length - 1].style.width = oWidth + "px";
        cDivs[cDivs.length - 1].style.height = oHeight + "px";
        cDivs[cDivs.length - 1].style.position = "absolute";
        cDivs[cDivs.length - 1].style.left = xPos + "px";
        cDivs[cDivs.length - 1].style.top = yPos + "px";
        cDivs[cDivs.length - 1].style.backgroundColor = "#999999";
        cDivs[cDivs.length - 1].style.opacity = 0.6;
        cDivs[cDivs.length - 1].style.filter = "alpha(opacity=60)";
        d.appendChild(cDivs[cDivs.length - 1]);
    };

    AChecker.shuffle = function (){
        var $grid = $('#shuffle_grid ul'),
            $filterOptions = $('.filter-options'),

        init = function() {
            $grid.shuffle({
                itemSelector: '.shuffle-item',
                speed: 0.1, // Transition/animation speed (milliseconds).
                gutterWidth: 0, // A static number or function that tells the plugin how wide the gutters between columns are (in pixels).
                columnWidth: 0, // A static number or function that returns a number which tells the plugin how wide the columns are (in pixels).
                initialSort: '.sort-options', // Shuffle can be initialized with a sort object. It is the same object given to the sort method.
                supported: false
            });
            setTimeout(function() {
                setupFilters();
                setupSearching();
                setupSorting();
                setupActions();
            }, 200);
        },
        filter = function($el, val, shuffle){
            if (shuffle.group !== 'all' && $.inArray(shuffle.group, $el.data('groups'))===-1)
                return false;
            var text = $.trim($el.text()).toLowerCase()+';'+
                'tag:'+$el.data('tagname')+'!'+
                'check:'+$el.data('checkpoint')+'!'+
                'line:'+$el.data('line')+'!';
            return (text.indexOf(val) !== -1);
        },
        setupFilters = function() {
            var $btns = $filterOptions.children('[data-group]');
            $btns.on('click', function() {
                var $this = $(this),
                    isActive = $this.hasClass('active'),
                    group = isActive ? 'all' : $this.data('group');

                // Hide current label, show current label in title
                if (!isActive)
                    $('.filter-options .active').removeClass('active');

                $this.toggleClass('active');

                // Filter elements
                var val=$('.js-shuffle-search')[0].value.toLowerCase();
                $grid.shuffle('shuffle', function ($el, shuffle) {
                    shuffle.group=group;
                    return (filter($el, val, shuffle));
                });
                $grid.sort();
                return false;
            });
            $btns = null;
        },
        setupSearching = function() {
            var wto;
            $('.js-shuffle-search').on('keyup change', function () {
                clearTimeout(wto);
                var val=this.value.toLowerCase();
                wto=setTimeout(function() {
                    $grid.shuffle('shuffle', function ($el, shuffle) {
                        return (filter($el, val, shuffle));
                    });
                    $grid.sort();
                },500);
            })
        },
        setupSorting = function() {
            $('.sort-options').on('change', function () {
                var sort = this.value,
                    opts = {};
                if (sort === 'checkpoint') {
                    opts = {
                        reverse: false,
                        by: function ($el) {
                            return parseInt($el.data('checkpoint'));
                        }
                    };
                } else if (sort === 'tagname') {
                    opts = {
                        by: function ($el) {
                            return $el.data('tagname').toLowerCase();
                        }
                    };
                } else if (sort === 'line') {
                    opts = {
                        reverse: false,
                        by: function ($el) {
                            return parseInt($el.data('line'));
                        }
                    };
                }
                // Filter elements
                $grid.shuffle('sort', opts);
            });
        },
        setupActions=function(){
            $grid.parent('div').find('h4').click(function() {
                var $parentDiv = $(this).parent('div');
                if ($parentDiv.find('.extra-info:visible').length === $parentDiv.find('li').length)
                    $parentDiv.find('.extra-info').hide();
                else
                    $parentDiv.find('.extra-info').show();
                $grid.shuffle('layout');
            });
            $grid.find('.error-more').click(function() {
                var check = $(this).parent('div').parent('li').data('checkpoint');
                if ($grid.find('[data-checkpoint="'+check+'"] .error-info:visible').length === $grid.find('[data-checkpoint="'+check+'"]').length)
                    $grid.find('[data-checkpoint="'+check+'"] .extra-info').hide();
                else
                    $grid.find('[data-checkpoint="'+check+'"] .extra-info').show();
                $grid.shuffle('layout');
            });
            $grid.find('li').click(function() {
                $(this).find('.extra-info').toggle();
                $grid.shuffle('layout');
            });
            $('a, tr, input, label').click(function (event) {
                event.stopPropagation();
            });
            $grid.on('layout.shuffle', function(event){
                var $parentDiv=$(this).parent('div');

                // Count elements per section
                var qt=$(this).find('li.filtered').length;
                if (qt>0){
                    $parentDiv.find('.review-qt').text(qt);
                    $parentDiv.find('h4').show();
                } else
                    $parentDiv.find('h4').hide();
            });
            $grid.shuffle('layout');
        };

        return {
            init: init
        };
    };
})(jQuery);

var $stopPropagation=false;
$(document).ready(function() {
    $(".extra-info").hide();
    AChecker.shuffle().init();
});