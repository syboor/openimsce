function JQK_Width () 
{
  var myWidth = $("#dbm_div").width() + 16;
  if ($('.fancybox-wrap:visible').length > 0 && 
      typeof $(".fancybox-iframe")[0].contentWindow != "undefined" && 
      typeof $(".fancybox-iframe")[0].contentWindow.JQK_Width == "function") {
    var parentWidth = $(".fancybox-iframe")[0].contentWindow.JQK_Width ();
    return Math.max (parentWidth, myWidth) + 100;
  } else if ($('.fancybox-wrap:visible').length > 0 && // TODO: Work in progress
             typeof $(".fancybox-iframe")[0].contentDocument != "undefined" && 
             typeof $(".fancybox-iframe")[0].contentDocument.JQK_Width == "function") {
    var parentWidth = $(".fancybox-iframe")[0].contentDocument.JQK_Width ();
    return Math.max (parentWidth, myWidth) + 100;
  } else {
    return myWidth;
  }
}

document.JQK_Width = window.JQK_Width;

function JQK_Height () 
{
  var myHeight = $("#dbm_div").height() + 40;
  if ($('.fancybox-wrap:visible').length > 0 && $(".fancybox-iframe")[0].contentWindow.eval ('typeof window.JQK_ResizeMe') == "function") { // Child fancybox is active
    var parentHeight = $(".fancybox-iframe")[0].contentWindow.JQK_Height ();
    return Math.max (parentHeight, myHeight) + 100;   
  } else {
    return myHeight;
  }
}

document.JQK_Height = window.JQK_Height;

function JQK_ResizeMe()
{
  if (parent.$ != undefined) {
    var frame = parent.$(".fancybox-iframe")[0];
    if (frame) {
      var iFrameContentHeight = JQK_Height ();
      var iFrameContentWidth = JQK_Width ();
  
      var outer = parent.$('.fancybox-wrap');
      var inner = parent.$('.fancybox-inner');
      var paddingTotal = 20;

      if (iFrameContentHeight > 0 && iFrameContentWidth > 0) {
        outer.css({
          height: iFrameContentHeight + paddingTotal,
          width: iFrameContentWidth + paddingTotal
         });
         inner.css({
           height: iFrameContentHeight,
           width: iFrameContentWidth
         });
         parent.$.fancybox.reposition();          
      }
    }
  }
  setTimeout(function(){ 
    JQK_ResizeMe();
  } ,100);
}

function JQK_AutocompleteList(fieldname, source, defaultlabel, defaultvalue, maxresults, columns, multi) {
  /* Autocomplete for OpenIMS list fields and FK fields. Not for text fields or list fields with an option "other". 
   *
   * Parameters:
   * - fieldname. This function assumes a text input field with name and id "fieldauto_"+fieldname, and a hidden input field with 
   *   name and id "field_"+fieldname.
   * - source: an url that returns (JSON) an array of items, given the parameters "term" and "limit".
   * - an item is an object with members: 
   *     value: internal value (to be stored in hidden field with id "field_"+fieldname).
   *     label: visible value, shown in textfield "fieldauto_"+fieldname; shown in dropdown when not using columns.
   *     longlabel: (optional) shown in dropdown instead of label (when not using columns)
   *     class: (optional) css class to be used in dropdown
   *     unselectable: (optional) item can not be highlighted / clicked. Unselectable items should occur at the end of
   *                  the items array; there should never be any selectable items after an unselectable item.
   *     column: (optional) array of visible values in each column.
   *   When using columns, the parameter "columns" must contain the names of the columns. Items may completely omit 
   *   the column property, in which case longlabel || label will be used and will span all the columns. But if an 
   *   item has a "column" property, the number of elements MUST match the "columns" parameter.
   * - defaultlabel, defaultvalue: these will be used after an invalid or empty value has been entered. Note that this function 
   *     does not set initial values, the server side created HTML should already contain the correct initial values.
   *     When using the "multi" option, defaultlabel will be shown in the input field, but defaultvalue will not be used.
   * - maxresults: how many suggestions to show in the dropdown
   * - columns: array with the names for the columns
   * - multi. Should be true if multiple items can be selected. Requires the existence of a div element "divdyn_selected_"+fieldname.
   *
   * Terminology:
   * - hidden field:  Hidden input field with name and id "field_"+fieldname. Contains internal value of selected item.
   *                  When using "multi", contains a ; separated list of internal values of all selected items.
   * - input field:   Text input field with name and id "fieldauto_"+fieldname, into which the user types text to search/select an item.
   * - visible field: Shows the selected item(s).
   *                  Non multi: same as input field.
   *                  Multi: a div element "divdyn_selected"+fieldname. Does not accept user input.
   */

  var topitem = false;   // the top item in the menu, the last time the menu was shown
  var topquery = false;  // the query string at the time topitem was saved. can be compared with "current" query string to decide if the topitem is still appropriate for the query.
  var lastquery = false; // the query string the last time the field lost focus, before any correction/completion was carried out by this script. should be restored when the field receives focus again.
  var blockingvalidation = false; // but will be true during the submit event
  
  var freshmenu; // Time when the menu was opened (milliseconds). False if we believe the user had a "real" interaction with the menu,
                 // meaning that he moved the mouse into it or used the keyboard. However, if the mouse just "happened" to already be 
                 // there when the menu opened (using tab), freshmenu contains the timestamp of opening.
  var nextfreshmenu;

  var servertimeout = 5000; // ms
  var lastajaxstart = 0;

  var selecteditems = [];   // array of selected items
  var selecteditemsul; // list showing selected items (only with "multi")

  var update_fields = function(value, label) {
    // Set both the input field and the hidden field

    // Show the selected value in the input field
    $("#fieldauto_"+fieldname).val(label);

    // Store the value in the hidden field.
    $("#field_"+fieldname).val(value);

    // Additionally, store the value just set in the input field in the hidden field,
    // so that we can detect it if the input field changes without an update
    // to the hidden field. (possible if somebody types and immediately tabs out
    // of the field)
    $("#field_"+fieldname).data("label", $("#fieldauto_"+fieldname).val());

  };

  var reset_fields = function() {
    if (multi) {
      // Do NOT update the hidden field
      $("#fieldauto_"+fieldname).val(defaultlabel);
      $("#field_"+fieldname).data("defaultlabel", $("#fieldauto_"+fieldname).val());
    } else {
      update_fields(defaultvalue, defaultlabel);
    }
  }
  
  var addreplace_item = function(item) {
    // For "multi", add the item to the selected items
    // For non multi, replace the selected item with item
    if (multi) {
      var currentvalues = $.map(selecteditems, function(item) { return item.value;});
      if ($.inArray(item.value, currentvalues) == -1) {
        selecteditems.push(item);
        selecteditems.sort(multi_compareitems);
        multi_updateselection();
      }

      var newvalues = $.map(selecteditems, function(item) { return item.value;});
      var newvalue = newvalues.join(";");
      update_fields(newvalue, defaultlabel);
    } else {
      update_fields(item.value, item.label);
    }
  }

  var multi_removeitem = function(value) {
    for (var i=0;i<selecteditems.length;i++) {
      if (value == selecteditems[i].value) {
        selecteditems.splice(i,1);
        break;
      }
    }
    var newvalues = $.map(selecteditems, function(item) { return item.value;});
    var newvalue = newvalues.join(";");
    update_fields(newvalue, defaultlabel);
    multi_updateselection();
  }

  var multi_updateselection = function() {
    // does NOT update the hidden value
    selecteditemsul.empty();
    for (var i=0;i<selecteditems.length;i++) {
      var item = selecteditems[i];
      var li = $("<li style='display:inline;float:left;margin:0 3px 3px 0;'></li>").appendTo(selecteditemsul);
      var span =  $("<span class='itemlabel' style=''></span>").text(item.label).appendTo(li);
      var deletebutton = $("<span class='ui-icon ui-icon-close' style='display:inline;'>&#8195;</span>").data("item.autocomplete", item).appendTo(li);
      //li.mouseover(function() { $(this).css("text-decoration", "line-through").css("opacity", "0.5"); });
      //li.mouseout(function() { $(this).css("text-decoration", "none").css("opacity", "1"); });
      deletebutton.click(function() { multi_removeitem($(this).data("item.autocomplete").value); return false; });
      // TODO: some mechanism for removing the items
      // TODO: some style
    }
  }

  // For "multi" we need to be able to do client side sorting
  var multi_compareitems = function (itema, itemb) {
    var labela = itema.label.toLowerCase();
    var labelb = itemb.label.toLowerCase();
    if (labela < labelb) { return -1; }
    if (labela > labelb) { return 1; }
    return 0;
  }


  var multi_init = function() {
    selecteditemsul = $("<ul id='ul_selected_"+fieldname+"' style='list-style:none; padding: 0; margin: 0;'></ul>").appendTo($("#divdyn_selected_"+fieldname));
    if ($("#field_"+fieldname).val()) {
      $.ajax({
        url: source,
        dataType: "json",
        data: {
          items: $("#field_"+fieldname).val()
        },
        async: false,
        success: function( data ) {
          //console.log("ajax success");
          if (data && data[data.length-1].value && !data[data.length-1].unselectable) {
            selecteditems=data;
            selecteditems.sort(multi_compareitems);
            //console.log(selecteditems);
            multi_updateselection();
            reset_fields(); // Since the field has no focus, it should revert to a reset state (showing defaultlabel if present)
          } else {
            // if the last returned item is unselectable, we have an unexpected error. Ignore other items.
          }
        }
      });
    }
  }

  if (multi) multi_init();
  
  // Do an "update" using the "initial" value of the fields, to prevent unnecessary validation if the user tabs through the fields without changing them
  update_fields($("#field_"+fieldname).val(), $("#fieldauto_"+fieldname).val());

  var check_consistency = function() {
    // Returns true if the fields (input and hidden) are consistent, meaning that
    // both were set through the update_fields function.
    // If the user modified the input field since then, this will return false.
    return ($("#field_"+fieldname).data("label") == $("#fieldauto_"+fieldname).val());
  };
  
  $("#fieldauto_"+fieldname).autocomplete({
    source: source,
    minLength: 0,
    position: {my: "left top", at: "left bottom", collision: "none none"}, // or maybe "none flip" ?
    focus: function(event, ui) {
      // Called when an item in the dropdown receives focus. Test whether this is key up/down or just mouse hover.
      // Note the extra layer of indirection compared with the UI source. Event type: autocomplete focus <- menufocus <- mouseenter/keydown <- mousemove 
      // We can not distinguish between "mouse moved into the menu" and "mouse was already there when the menu opened" based on
      // the event chain. Instead, we use timing (how long since the menu opened) and repetition (if we receive two events, the second one
      // must be a mouse move) to decide whether the menu should be considered "fresh" (no user interaction with it) or not.
      if ( /^key/.test(event.originalEvent.originalEvent.type)) { // is this a keyboard event
        $("#fieldauto_"+fieldname).val(ui.item.label);
        freshmenu = false;
      }
      if (nextfreshmenu == false) freshmenu = false;

      if (freshmenu) { // If its been a while since the menu opened, this event must be due to a real mouse move and the menu is no longer fresh.
        var d = new Date();
        var offset = d.getTime() - freshmenu; // in milliseconds
        if (offset > 400) freshmenu = false;
      }

      // If the menu is fresh, no item should be highlighted
      if (freshmenu) {
       $("#ui-active-menuitem").removeClass("ui-state-hover");
        nextfreshmenu = false; // Whatever happens, the next event will be due to a real mouse move.
      }     

      return false;
    },
    select: function(event, ui) {

      // This event fires when the user clicks an item in the menu, or when he leaves the field (tab) while an item 
      // in the menu was highlighted, or would have been highlighted were it not for our "freshmenu" modification to focus.
      // We do not want to process the select event if the mouse "happens to be" somewhere under the field prior to tabbing into
      // the field, and the user leaves the field by tabbing out, without ever moving the mouse or using the keyboard arrows.

      // In case of selecting through hover + tab / enter, save the query so that it can be restored later.
      // In case of selecting through keydown + tab / enter, the query has already been updated/completed through the focus 
      //   event, and this updated query is not saved.
      // In case of selecting through mouseclick, the query is not saved.
      lastquery = false;
      if (!freshmenu) {
        // Save query so that it can be restored later
        if ( /^key/.test(event.originalEvent.originalEvent.type)) { // is this a keyboard event
          if ($("#fieldauto_"+fieldname).val() != ui.item.label) lastquery = $("#fieldauto_"+fieldname).val();
        }
        // Select item
        addreplace_item(ui.item);
      }

      return false; // important. otherwise value instead of label is used.
    },
    open: function(event, ui) { 
      // When the dropdown menu is opened, remember what the top item was.
      // So that, if the user tabs out of the field, we can use the top item. 
      var that = this;
      var menu = $(this).data("autocomplete").menu;
      topitem = menu.element.children(".ui-menu-item").first();
      topquery = $("#fieldauto_"+fieldname).val();

      var d = new Date();
      freshmenu = nextfreshmenu = d.getTime();

      // The menu seems to remain open sometimes (hard to reproducer).
      // Set a function to check whether the field still has focus and close the menu if not.
      var checkfocus = function() {
        if (document.activeElement.id == "fieldauto_" + fieldname) {
          setTimeout(checkfocus, 300);
        } else if ($(that).data("autocomplete").menu.element.is(":visible")) {
          $(that).data("autocomplete").close();
        }
      };
      setTimeout(checkfocus, 300);
    },
    close: function(event, ui) {
      topitem = topquery = false;
    },
    search: function(event, ui) {
      // After this function returns, JQuery UI does an ajax request to retrieve the drop down menu
      var d = new Date();
      lastajaxstart = d.getTime();
      // Extra timeout to remove "loading" class, just in case. With properly configured ajax timeout, I couldnt
      // produce a situation where this was needed.
      setTimeout(function() {
        var d = new Date();
        if (lastajaxstart + servertimeout < d.getTime()) $("#fieldauto_"+fieldname).removeClass( "ui-autocomplete-loading");
      }, servertimeout + 100);

      // Temporarily set global ajax timeout, than restore the original after JQuery UI has created the request.
      var oldtimeout = $.ajaxSettings.timeout;
      $.ajaxSetup({timeout: servertimeout});
      setTimeout(function() { $.ajaxSetup({timeout: oldtimeout}); }, 50);
      return true;
    }
  });

  // This function can be called from the "Kies..." popup
  $("#fieldauto_"+fieldname).data("autocomplete")._select = function(value, label) {
    var item = { value: value, label : label };
    addreplace_item(item);
  }

  // Render an item, using columns, but without breaking the ul > li > a structure. So everything we add needs to be
  // "inside" the a element, and later on some javascript is used to align everything.
  $("#fieldauto_"+fieldname).data("autocomplete")._renderItem = function( ul, item ) {
    var li = $("<li></li>").data("item.autocomplete", item).appendTo(ul);
    var a = $("<a></a>").appendTo(li);
    var span;
    if (item["class"]) a.addClass(item["class"]);
    if (columns && item.columns) {
      for (var i=0;i<item.columns.length;i++) {
        span = $('<span class="autocompletecolumn"></span>').text(item.columns[i]);
        if (i > 0) {
          span.css('padding-left', '15px');
        }
        span.appendTo(a);
      }
    } else {
      $('<span></span>').text(item.longlabel || item.label).appendTo(a);
    }
  };

  // ResizeMenu extra stuff:
  // - add column names. This can not be done in _renderMenu, because li-elements added in _renderMenu will
  //   have the class "ui-menu-item" and a bunge of events added to it. ResizeMenu is "late enough" that if
  //   we add stuff, it will be completely unselectable and it will even be "skipped over" when using the down
  //   arrow. :) 
  // - resize the entire window if it is too small to contain the menu.
  $("#fieldauto_"+fieldname).data("autocomplete")._resizeMenu = function() {
    var ul = this.menu.element;
    var maxwidth, li, a, span, addcolumnnames;

    if (columns) {
      // Do not add column names if the menu is empty, or if it contains a single unselectable item, i.e. "Geen resultaten"
      if (ul.children().length == 0 || (ul.children().length == 1 && ul.children().first().data("item.autocomplete").unselectable)) {
        addcolumnnames = false;
      } else {
        addcolumnnames = true;
      }

      if (addcolumnnames) {
        li = $('<li class="ui-menu-header"></li>').prependTo(ul);
        a = $("<a></a>").appendTo(li);
      }
      for (var i=0;i<columns.length;i++) {
        maxwidth = 0;
        if (addcolumnnames) {
          span = $('<span class="autocompletecolumn"></span>').text(columns[i]);
          if (i > 0) {
            span.css('padding-left', '15px');
          }
          span.appendTo(a);
        }

        // Determine the longest item in each column (including the column name we just added),
        // and add enough padding to the others items to make the column line up
        ul.find("span.autocompletecolumn:nth-child("+(i+1)+")")
          .each(function() { maxwidth = Math.max(maxwidth, $(this).width()); })
          .each(function() { $(this).css("padding-right", maxwidth - $(this).width()); });
         
      }
    }

    // Standard stuff to make certain menu is never smaller than the field above it.
    ul.outerWidth( Math.max(
      ul.width( "" ).outerWidth(),
      this.element.outerWidth()
    ));

    // Resize the window if needed.  For some reason, document width in quirksmode includes some 20 pixels that window width does not include.
    // Height of the floating ul is not measured correctly, so height is completely ignored for now.
    // Needs a small delay, because the first time _resizeMenu is called, the menu is not yet positioned, so its effect on the 
    // document width yet can not be measured yet.
    setTimeout(function() {
      var newwidth = Math.min(1000, $(document).width());
      if (newwidth > $(window).width() + 20) {
        // 20:  some extra pixels to make room for the vertical scroll bar that appears because I cant get vertical resizing to work
        window.resizeBy(newwidth - $(window).width() + 20, 0);
      }
    }, 10);
  };


  // Add support for "non-selectable" information in the list
  $("#fieldauto_"+fieldname).data("autocomplete").menu.activate = function(event,item) {
    if (item.data("item.autocomplete").unselectable) return false;
    $.ui.menu.prototype.activate.call(this, event, item);
  };

  //$("<div style=\"clear: both;\">Test</div>").appendTo($("body")); // attempt to fix height measuring, didnt work
  
  // Add id to element so that multiple fields on each pages can each be styled differently
  $("#fieldauto_"+fieldname).data("autocomplete").menu.element.attr("id", "ul_" + fieldname);

  function updateonblur() {
    // Whenever the field looses focus, make certain that visible and hidden field have consistent and valid values.
    // If we do anything to the fields here, save the query that we are about to modify so that we can restore it later.

    var currentquery = $("#fieldauto_"+fieldname).val();
    if (currentquery === "") { // Special case for "empty" field
      reset_fields();
      lastquery = currentquery;
    } else if (!check_consistency()) { // The fields are consistent if they were both set through update_fields and none of them has changed since then.
      lastquery = currentquery;
      // Check if the query matches the last topitem/topquery saved. If so, use topitem to set the hidden field.
      // This happens if the user types something, waits for the dropdown menu to appear, and then tabs out of 
      // the field without clicking anything in the menu. So to us, Tab means "accept the first suggestion".
      if (topitem && topquery == lastquery) {
        // If the menu was still open when loosing focus, use the (remembered) top item from the menu.
        if (topitem.data("item.autocomplete").unselectable) {
          reset_fields();
        } else {
          //update_fields(topitem.data("item.autocomplete").value, topitem.data("item.autocomplete").label);
          addreplace_item(topitem.data("item.autocomplete"));
        }
      } else { // Do a query and use the top result
        var d = new Date();
        var thisajaxstart = lastajaxstart = d.getTime();
        // Do a ajax call to find out if the current query matches something, and what the top match would be.
        // Use that top match.
        // If multiple ajax request are fired for the same field, we are only interested in the result of the LAST one, 
        // the earlier request should be "cancelled".
        $("#fieldauto_"+fieldname).addClass("ui-autocomplete-loading");
        $.ajax({
          url: source,
          dataType: "json",
          data: {
            term: lastquery,
            limit: 1
          },
          async: !blockingvalidation,
          success: function( data ) {
            //console.log("ajax success");
            if (thisajaxstart >= lastajaxstart) {
              if (data && data[0].value && !data[0].unselectable) {
                //console.log("ajax update");
                //update_fields(data[0].value, data[0].label);
                addreplace_item(data[0]);
              } else {
                //console.log("ajax defaultlabel");
                reset_fields(); 
              }
              $("#fieldauto_"+fieldname).removeClass( "ui-autocomplete-loading");
            } else {
            }
          },
          error: function() {
            if (thisajaxstart >= lastajaxstart) {
              reset_fields();
              $("#fieldauto_"+fieldname).removeClass( "ui-autocomplete-loading");
            }
          },
          timeout: servertimeout
        });
        // Extra timeout function to remove "loading" class after servertimeout.
        setTimeout(function() {
          var d = new Date();
          if (lastajaxstart + servertimeout < d.getTime()) $("#fieldauto_"+fieldname).removeClass( "ui-autocomplete-loading");
        }, servertimeout + 100);          
      }
    }
    return true;
  }
  
  $("#fieldauto_"+fieldname).blur(function(event) {
    // If the user clicks a link in the menu, this (blur) event fires before the "select" event on the menu. 
    // (OTOH if the user uses key down + "enter" to select an item, the field keeps focus so there is no blur, 
    //  and if he uses key down + tab, the "select" event fires before the blur event.)
    // We shouldnt need to do anything, since the "select" event will result in valid values in both fields.
    // Unfortunately, sometimes there is a close and open event before the select, which confuses our
    // "freshmenu" diagnosis. So just do the select ourselves.

    if ($("#ui-active-menuitem").length && !freshmenu) {
      $("#fieldauto_"+fieldname).data("autocomplete").menu.select(event, { item: $("#ui-active-menuitem").parent().data("item.autocomplete") });
      setTimeout(function() { $("#fieldauto_"+fieldname).focus(); }, 10); // keep focus on field
    } else {
      updateonblur();
    }
    return true;

  });

  $("#fieldauto_"+fieldname).focus(function(event) {

    if (lastquery && lastquery != $("#fieldauto_"+fieldname).val()) {
      // Restore last user query (if it was overwritten by tab, not by clicking)
      $("#fieldauto_"+fieldname).val(lastquery);
      // And open the drop down menu // TODO: only if menu open when leaving field
      $("#fieldauto_"+fieldname).data("autocomplete").search(lastquery);
      lastquery = false; // Because focus also gets called when clicking an item in the menu
      // TODO: somehow, in IE, the text cursor jumps from the end of the field to the beginning of the field, but this happens "long" after this event. No idea why. 
    } else if ($("#fieldauto_"+fieldname).val() == defaultlabel && (multi || $("#field_"+fieldname).val() == defaultvalue)) {
      // If the field is showing the default label ("Zoeken..."), 
      // remove it when the field receives focus so that the user can start typing
      $("#fieldauto_"+fieldname).val("");
    } 
    return true;
  });
  
  // Add an event to the submit of the form containing this field.
  // This is needed because if a user presses Enter in the field, the blur event
  // of the field never fires.
  $("form:has(#fieldauto_"+fieldname+")").submit(function() {
    // Fire blur event. 
    var oldlabel = $("#fieldauto_"+fieldname).val();
    blockingvalidation = true; // ajax calls during the blur() should be synchronous, so they finish before the form gets submitted
    $("#fieldauto_"+fieldname).blur(); 
    blockingvalidation = false;
    var newlabel = $("#fieldauto_"+fieldname).val();
    var dosubmit = ((oldlabel == newlabel) || (oldlabel == "" && newlabel == defaultlabel));
    // TODO: if the blur event changed something (i.e. rejected the users input), cancel the submit
    // so the user can see and review the changes. Problem: this seems to not play nicely with the 
    // knopStop onclick event elsewhere in OpenIMS.
    return true;
  });
  
}