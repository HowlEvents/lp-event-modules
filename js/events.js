var EVENTS = [];
var ROOMS = [];
var ROOM_NAMES = [];
var TRACKS = ["Con Services", "General", "Music/Performance", "Art", "Fursuit", "Socials", "Events", "Gaming"];
var COLORS = ["4affba", "57ff4f", "ffaa00", "eeee22", "3f8cea", "00eeee", "a083e2", "ff6666"];
var BORDER_COLORS = [];
var OFFSET = 0;

if(jQuery('.events-wrapper').length > 0) {
  jQuery.getJSON("https://codeimp-conventionservice-dev.azurewebsites.net/api/public/GetPublicConventionEvent?shortName=HOWL2022", function(data) {
    jQuery('.events-wrapper .loading').remove();
    processAndSaveData(data);
    initEventsCal();
    initMySchedule();
  });
  // jQuery.getJSON("https://codeimp-conventionservice-dev.azurewebsites.net/api/public/GetPublicConventionEvent?shortName=HOWL2022", function(rawData) {
  //   console.log(rawData);
  //   processAndSaveData(rawData);
  // });
  function sortRooms(a, b){
    if (a.order === b.order) {
      return 0;
    }
    else {
      return (a.order < b.order) ? -1 : 1;
    }
  }
  function processAndSaveData(rawData) {
    OFFSET = rawData.timezone * 60;

    for (let i = 0; i < COLORS.length; i++) {
      BORDER_COLORS[i] = LightenDarkenColor(COLORS[i], -20);
    }

    window.scheduleRenderer.TRACKS = TRACKS;
    window.scheduleRenderer.COLORS = COLORS;
    window.scheduleRenderer.BORDER_COLORS = BORDER_COLORS;

    // Enumerate Rooms
    rawData.rooms = rawData.rooms.sort(sortRooms)
    for (let i = 0; i < rawData.rooms.length; i++){
      ROOMS[i] = rawData.rooms[i].roomViewId;
      ROOM_NAMES[i] = rawData.rooms[i].name;
    }
    window.scheduleRenderer.ROOMS = ROOMS;
    window.scheduleRenderer.ROOM_NAMES = ROOM_NAMES;

    // Enumerate Events
    for (let i = 0; i < rawData.schedules[0].scheduleEvents.length; i++) {
      let activity = rawData.activities.find(x => x.id === rawData.schedules[0].scheduleEvents[i].eventId);
      let room = rawData.rooms.find(x => x.id === rawData.schedules[0].scheduleEvents[i].roomId);
      let track = TRACKS[(COLORS.indexOf(activity.colorCode) >= 0 ? COLORS.indexOf(activity.colorCode) : 1)];
      EVENTS[i] = {
        "id": rawData.schedules[0].scheduleEvents[i].id,
        "title": activity.name,
        "start_time": correctOffset(rawData.schedules[0].scheduleEvents[i].startDate),
        "end_time": correctOffset(rawData.schedules[0].scheduleEvents[i].endDate),
        "description": activity.description,
        "host": activity.hostName,
        "room_name": room.roomViewId,
        "track": track,
        "color": activity.colorCode,
        "border_color": LightenDarkenColor(activity.colorCode, -20),
        // "ticket_purchase_required": false,
        // "foal_recommended": false
      }
    }
    init_pdf_schedule_renderer();
  }
  function correctOffset(t) {
    let nt = new Date(new Date(t).getTime() + (OFFSET * 60000));
    return nt.toISOString().slice(0, -5);
  }
  function LightenDarkenColor(col, amt) {
    var usePound = false;
    if ( col[0] == "#" ) {
      col = col.slice(1);
      usePound = true;
    }

    var num = parseInt(col,16);

    var r = (num >> 16) + amt;

    if ( r > 255 ) r = 255;
    else if  (r < 0) r = 0;

    var b = ((num >> 8) & 0x00FF) + amt;

    if ( b > 255 ) b = 255;
    else if  (b < 0) b = 0;

    var g = (num & 0x0000FF) + amt;

    if ( g > 255 ) g = 255;
    else if  ( g < 0 ) g = 0;

    var hex = (g | (b << 8) | (r << 16)).toString(16);

    return (usePound?"#":"") + hex.padStart(6, '0');
  }
  function TextColor(c) {
    var rgb = parseInt(c, 16);   // convert rrggbb to decimal
    var r = (rgb >> 16) & 0xff;  // extract red
    var g = (rgb >>  8) & 0xff;  // extract green
    var b = (rgb >>  0) & 0xff;  // extract blue

    var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709

    if (luma < 128) {
      return 'ffffff';
    }
    return '000000';
  }
  function customHyphen(s) {
    return s;//.replace('Parasprite', 'Para\u00ADsprite').replace('Seashell','Sea\u00ADshell').replace('MLP:CCG','MLP:\u200BCCG');
  }
  function formatTime(d) {
    var h = d.hour;
    var m = d.minute;
    var suffix = (h >= 12 ? 'PM' : 'AM');
    if (h == 0) { h = 12; }
    if (h > 12) { h -= 12; }
    if (h < 10) {h = '0' + h;}
    if (m < 10) {m = '0' + m;}
    return h + ':' + m + ' ' + suffix;
  }
  function parseDate(d) {
    var regex = /^([0-9]+)-([0-9]+)-([0-9]+)T([0-9]+):([0-9]+):([0-9]+)$/;
    var match = regex.exec(d);
    if (match == null) {
      debugger;
      throw new Exception("Could not parse date " + d);
    }
    return {
      year: parseInt(match[1]),
      month: parseInt(match[2]),
      day: parseInt(match[3]),
      hour: parseInt(match[4]),
      minute: parseInt(match[5]),
      second: parseInt(match[6])
    }
  }
  function cloneDate(d) {
    return {
      year: d.year,
      month: d.month,
      day: d.day,
      hour: d.hour,
      minute: d.minute,
      second: d.second
    };
  }
  function dateToConDay(d) {
    var conDay = d.day - 4;
    if (d.hour <= 2) { conDay -= 1; }
    return conDay;
  }
  function dateDelta(a, b) {
    return Date.UTC(a.year, a.month, a.day, a.hour, a.minute, a.second) - Date.UTC(b.year, b.month, b.day, b.hour, b.minute, b.second);
  }

  var START_TIME = 9;
  var DAY_LENGTH = 18;
  var CELL_WIDTH = 120;
  var CELL_HEIGHT = 24;
  var HEADER_ROW_HEIGHT = 54;
  function initTrackLegend() {
    legend = jQuery('#track-key');
    for (var i = 0; i < TRACKS.length; i++) {
      var item = document.createElement('li');
      var c = document.createElement('div');
      c.className = 'legend-color';
      c.style.cssText = 'background-color:#' + COLORS[i] + ';'
              + 'border-color:#' + BORDER_COLORS[i] + ';';
      item.appendChild(c);
      item.appendChild(document.createTextNode(TRACKS[i]));
      legend.append(item);
    }
  }

  function initEventsCal() {
    initTrackLegend();
    ['cal-fri', 'cal-sat', 'cal-sun'].forEach(function(id) {
      var row = document.createElement('div');
      row.className = 'cal-row';
      var cell = document.createElement('div');
      cell.className = 'cell left-cell top-cell';
      row.appendChild(cell);
      for (var i = 0; i < ROOM_NAMES.length; i++) {
        var cell = document.createElement('div');
        cell.style.left = ((i+1)*(CELL_WIDTH+1)+1) + 'px';
        cell.style.top =  '0px';
        cell.className = 'cell top-cell roomHeader';
        cell.innerHTML = ROOM_NAMES[i];
        row.appendChild(cell);
      }
      document.getElementById(id).appendChild(row);
      for (var i = 0; i < DAY_LENGTH * 2; i++) {
        var row = document.createElement('div');
        row.className = 'cal-row';
        var timeCell = document.createElement('div');
        timeCell.style.left = '0px';
        timeCell.style.top =  (HEADER_ROW_HEIGHT+i*(CELL_HEIGHT+1)+1) + 'px';
        timeCell.className = 'cell left-cell ' + (i%2==0 ? 'even-cell' : 'odd-cell');
        if (i == DAY_LENGTH*2-1) {timeCell.className += ' bottom-cell';}
        timeCell.innerHTML = formatTime({hour: Math.floor(START_TIME+i/2) % 24, minute: i%2 == 0 ? 0 : 30});
        row.appendChild(timeCell);
        for (var j = 0; j < ROOM_NAMES.length; j++) {
          var cell = document.createElement('div');
          cell.style.left = ((j+1)*(CELL_WIDTH+1)+1) + 'px';
          cell.style.top = timeCell.style.top;
          cell.className = 'cell ' + (i%2==0 ? 'even-cell' : 'odd-cell');
          if (i == DAY_LENGTH*2-1) {cell.className += ' bottom-cell';}
          row.appendChild(cell);
        }
        document.getElementById(id).appendChild(row);
      }
      //document.getElementById(id).style.width = ((ROOM_NAMES.length+1) * (CELL_WIDTH+1) + 2) + 'px';
      document.getElementById(id).style.width = '100%';
      document.getElementById(id).style.height = (HEADER_ROW_HEIGHT + (DAY_LENGTH*2) * (CELL_HEIGHT+1) + 2) + 'px';
    });
    for (var i = 0; i < EVENTS.length; i++) {
      var d = parseDate(EVENTS[i].start_time);
      EVENTS[i].absStartTime = d.day*24*60 + d.hour*60 + d.minute;
      d = parseDate(EVENTS[i].end_time);
      EVENTS[i].absEndTime = d.day*24*60 + d.hour*60 + d.minute;
    }
    EVENTS.sort(function(a, b) {
      return a.absStartTime - b.absStartTime;
    });
    for (var i = 0; i < EVENTS.length; i++) {
      var event = EVENTS[i];
      var room_name = event['room_name'];
      var width = 1;
      var column = ROOMS.indexOf(room_name);
      if (column < 0) {
        if (event.absEndTime - event.absStartTime >= 240) { continue; }
        column = ROOM_NAMES.length-1;
      }
      var start_time = parseDate(event['start_time']);
      var end_time = parseDate(event['end_time']);
      var parentId = null;
      var conDay = dateToConDay(start_time);
      if (conDay == 0) {parentId = 'cal-fri'};
      if (conDay == 1) {parentId = 'cal-sat'};
      if (conDay == 2) {parentId = 'cal-sun'};
      if (parentId == null) {continue;}

      var display_end_time = cloneDate(end_time);
      var display_start_time = cloneDate(start_time);
      var late_night = false;
      var early_morning = false;
      if ((display_start_time.hour > display_end_time.hour || display_start_time.hour < 3) && display_end_time.hour > 3) { display_end_time.hour = 3; display_end_time.minute = 0; display_end_time.second = 0; early_morning = false; late_night = true; }
      if (display_start_time.hour > 3 && display_start_time.hour < 9) { display_start_time.hour = 9; display_start_time.minute = 0; display_start_time.second = 0; early_morning = true; late_night = false; }

      var height = Math.floor( dateDelta(display_end_time, display_start_time)/1000.0/60.0/30.0 * (CELL_HEIGHT +1)) - 12;
      width = width * (CELL_WIDTH+1) - 13;
      var x = (CELL_WIDTH + 1) + column * (CELL_WIDTH+1) + 1;
      var start_hour = display_start_time.hour;
      if (start_hour <= 2) { start_hour += 24; }
      var y = HEADER_ROW_HEIGHT + Math.floor( ((start_hour-START_TIME)*60 + display_start_time.minute)/30.0 * (CELL_HEIGHT+1) ) + 1;
      var title = event['title'];
      var clean_title = title.replace(/<[^>]*>/g, '');
      if (height < 20 && clean_title.length > 20) { title = clean_title.substring(0, 17) + '...'; }
      else if (clean_title.length > 30) {title = clean_title.substring(0, 27) + '...';}
      // if (event['ticket_purchase_required']) { title = "<img class='event-icon' src='/images/TicketIcon.svg'> " + title; }
      // if (event['foal_recommended']) { title = "<img class='event-icon' src='/images/FoalFriendlyIcon.svg'>" + title; }
      if (late_night) { title = "<img style='float:right;width:2em;margin: 0 0 2px 2px' src='/images/DownArrowIcon.svg'>" + title; }
      if (early_morning) { title = "<img style='float:right;width:2em;margin: 0 0 2px 2px' src='/images/UpArrowIcon.svg'>" + title; }

      var colorIndex = (event.track == null ? COLORS.length-1 : TRACKS.indexOf(event.track));

      var style = 'left:' + x + 'px;';
      style += 'top:' + y + 'px;';
      style += 'height:' + height + 'px;';
      style += 'width:' + width + 'px;';
      style += 'background:#' + (event.color ? event.color : COLORS[colorIndex]) + ';';
      style += 'border-color:#' + (event.border_color ? event.border_color : BORDER_COLORS[colorIndex]) + ';';
      style += 'color:#' + TextColor(event.color ? event.color : COLORS[colorIndex]) + ';';
      var eventDiv = document.createElement('div');
      eventDiv.className = 'event';
      eventDiv.style.cssText = style;
      eventDiv.innerHTML = "<div>" + customHyphen(title) + "</div>";
      jQuery(eventDiv.children[0]).hyphenate('en-us');
      document.getElementById(parentId).appendChild(eventDiv);
      var descDiv = document.createElement('div');
      // noinspection EqualityComparisonWithCoercionJS
      descDiv.innerHTML = '<i class="closeEvent"></i><div class="title">'
        + event['title'] + '</div><div class="time"><i class="dashicons dashicons-clock"></i> '
        + "Time: " + formatTime(start_time) + '-' + formatTime(end_time) + '</div><br/>'
        + event['description']
        // + (event['ticket_purchase_required'] ? "<br/><br/><img style='float:left;width:2em;margin: 0 2px 2px 0' src='/images/TicketIcon.svg'> A <a href='/registration/tickets'>ticket purchase</a> is required to attend this exclusive event!</a>" : '' )
        // + (event['foal_recommended'] ? "<br/><br/><img style='float:left;width:2em;margin: 0 2px 2px 0' src='/images/FoalFriendlyIcon.svg'> Everfree Northwest recommends this event for foals!<br/>" : '' )
        + ((event['host'] !== undefined && event['host'].length > 0) ? '<br><br>Hosted by ' + event['host'] : '')
        + "<br/><br/>";
      var aMySched = document.createElement('a');
      aMySched.href='#';
      aMySched.className = 'addto';
      aMySched.innerHTML = 'Add to my schedule';
      (function (event) {jQuery(aMySched).click(function() {
        if (jQuery.inArray(event, myEvents) < 0) {
          myEvents.push(event);
          redrawMySched();
        }
        jQuery(this.parentNode).click();
        return false;
      });})(event);
      descDiv.appendChild(aMySched);
      descDiv.className = 'eventDesc';
      (function(descDiv) {
        jQuery(eventDiv).click(function(e) {
          if (getSelection().toString()) { return false; }
          jQuery('.eventDesc').hide();
          jQuery(descDiv).show();
          var rect = e.target.getBoundingClientRect();
          var targetCal = jQuery(e.target).closest(".calendar");
          var calOffset = targetCal.offset();
          var offsetX = e.pageX - calOffset.left;
          var offsetY = e.pageY - calOffset.top;
          offsetX = Math.min(offsetX, targetCal.width() - 300);
          offsetY = Math.min(offsetY, targetCal.height() - descDiv.getBoundingClientRect().height);
          descDiv.style.left = offsetX + 'px';
          descDiv.style.top = offsetY + 'px';
        });
        jQuery(descDiv).click(function() {
          if (getSelection().toString()) { return false; }
          jQuery(descDiv).hide();
        });
      })(descDiv);
      document.getElementById(parentId).appendChild(descDiv);
    }
    jQuery('#tab-fri').click(function() {
      jQuery('#cal-sat').hide(); jQuery('#tab-sat').removeClass('selected');
      jQuery('#cal-sun').hide(); jQuery('#tab-sun').removeClass('selected');
      jQuery('#cal-fri').show(); jQuery('#tab-fri').addClass('selected');
    });
    jQuery('#tab-sat').click(function() {
      jQuery('#cal-fri').hide(); jQuery('#tab-fri').removeClass('selected');
      jQuery('#cal-sun').hide(); jQuery('#tab-sun').removeClass('selected');
      jQuery('#cal-sat').show(); jQuery('#tab-sat').addClass('selected');
    });
    jQuery('#tab-sun').click(function() {
      jQuery('#cal-fri').hide(); jQuery('#tab-fri').removeClass('selected');
      jQuery('#cal-sat').hide(); jQuery('#tab-sat').removeClass('selected');
      jQuery('#cal-sun').show(); jQuery('#tab-sun').addClass('selected');
    });
  }
  function initMySchedule() {
    var DAYS = ['Friday', 'Saturday', 'Sunday'];
    var row = document.createElement('div');
    row.className = 'cal-row';
    var cell = document.createElement('div');
    cell.className = 'cell left-cell top-cell';
    row.appendChild(cell);
    for (var i = 0; i < DAYS.length; i++) {
      var cell = document.createElement('div');
      cell.style.left = ((i+1)*(CELL_WIDTH+1)+1) + 'px';
      cell.style.top =  '0px';
      cell.className = 'cell top-cell roomHeader';
      cell.innerHTML = DAYS[i];
      row.appendChild(cell);
    }
    document.getElementById('mysched').appendChild(row);
    for (var i = 0; i < DAY_LENGTH * 2; i++) {
      var row = document.createElement('div');
      row.className = 'cal-row';
      var timeCell = document.createElement('div');
      timeCell.style.left = '0px';
      timeCell.style.top =  (HEADER_ROW_HEIGHT+i*(CELL_HEIGHT+1)+1) + 'px';
      timeCell.className = 'cell left-cell ' + (i%2==0 ? 'even-cell' : 'odd-cell');
      if (i == DAY_LENGTH*2-1) {timeCell.className += ' bottom-cell';}
      timeCell.innerHTML = formatTime({hour: Math.floor(START_TIME+i/2) % 24, minute: i%2 == 0 ? 0 : 30});
      row.appendChild(timeCell);
      for (var j = 0; j < DAYS.length; j++) {
        var cell = document.createElement('div');
        cell.style.left = ((j+1)*(CELL_WIDTH+1)+1) + 'px';
        cell.style.top = timeCell.style.top;
        cell.className = 'cell ' + (i%2==0 ? 'even-cell' : 'odd-cell');
        if (i == DAY_LENGTH*2-1) {cell.className += ' bottom-cell';}
        row.appendChild(cell);
      }
      document.getElementById('mysched').appendChild(row);
    }
    //document.getElementById('mysched').style.width = ((DAYS.length+1) * (CELL_WIDTH+1) + 2) + 'px';
    document.getElementById('mysched').style.width = '100%';
    document.getElementById('mysched').style.height = ((DAY_LENGTH*2) * (CELL_HEIGHT+1) + 2) + HEADER_ROW_HEIGHT + 'px';
    var ids = jQuery.cookie('HOWL_MS20');
    if (ids) {
      ids = ids.split(/,/);
      for (var i = 0; i < EVENTS.length; i++) {
        if (jQuery.inArray(''+EVENTS[i].id, ids) >= 0) {myEvents.push(EVENTS[i]);}
      }
      redrawMySched();
    }
  }
  var myEvents = [];
  function redrawMySched() {
    var ids = [];
    for (var i = 0; i < myEvents.length; i++) {ids.push(myEvents[i].id);}
    jQuery.cookie('HOWL_MS20', ids.join(','), {expires: 365});
    jQuery('#mysched div.event').remove();
    var processedEvents = [];
    for (var i = 0; i < myEvents.length; i++) {
      if (jQuery.inArray(myEvents[i], processedEvents) >= 0) { continue; }
      var column = dateToConDay(parseDate(myEvents[i].start_time));
      if (column < 0 || column > 2) {continue;}
      var eventsBlock = [myEvents[i]];
      var updated = 1;
      while (updated) {
        updated = 0;
        var start_time = parseDate(eventsBlock[0].start_time);
        var end_time = parseDate(eventsBlock[0].end_time);
        for (var j = 1; j < eventsBlock.length; j++) {
          var st = parseDate(eventsBlock[j].start_time);
          var et = parseDate(eventsBlock[j].end_time);
          if (dateDelta(start_time, st) > 0) {start_time = st;}
          if (dateDelta(end_time, et) < 0) {end_time = et;}
        }
        for (var j = 0; j < myEvents.length; j++) {
          if (jQuery.inArray(myEvents[j], processedEvents) >= 0) { continue; }
          if (jQuery.inArray(myEvents[j], eventsBlock) >= 0){ continue; }
          var st = parseDate(myEvents[j].start_time);
          var et = parseDate(myEvents[j].end_time);
          if ((dateDelta(st, start_time) >= 0 && dateDelta(st, end_time) < 0)
              || (dateDelta(st, start_time) < 0 && dateDelta(et, start_time) > 0)) {
              eventsBlock.push(myEvents[j]);
              updated = 1;
          }
        }
      }
      for (var j = 0; j < eventsBlock.length; j++) {
        var event = eventsBlock[j];
        var start_time = parseDate(event.start_time);
        var end_time = parseDate(event.end_time);
        var room_name = event['room_name'];
        var room_index = ROOMS.indexOf(room_name);
        if (room_index != -1) {
          room_name = ROOM_NAMES[room_index];
        } else {
          room_name = 'Other';
          room_index = ROOM_NAMES.length-1;
        }
        var display_end_time = cloneDate(end_time);
        var display_start_time = cloneDate(start_time);
        if ((display_start_time.hour > display_end_time.hour || display_start_time.hour < 3) && display_end_time.hour > 3) { display_end_time.hour = 3; display_end_time.minute = 0; display_end_time.second = 0; }
        if (display_start_time.hour > 3 && display_start_time.hour < 9) { display_start_time.hour = 9; display_start_time.minute = 0; display_start_time.second = 0; }

        var height = Math.floor( dateDelta(display_end_time, display_start_time)/1000.0/60.0/30.0 * (CELL_HEIGHT +1)) - 12;
        var width = CELL_WIDTH/eventsBlock.length - 12;
        var x = CELL_WIDTH + j*CELL_WIDTH/eventsBlock.length + column * (CELL_WIDTH+1) + 2;
        var start_hour = display_start_time.hour;
        if (start_hour <= 2) { start_hour += 24; }
        var y = HEADER_ROW_HEIGHT + Math.floor( ((start_hour-START_TIME)*60 + display_start_time.minute)/30.0 * (CELL_HEIGHT+1) ) + 1;
        var title = event['title'];
        var clean_title = title.replace(/<[^>]*>/g, '');
        if (height < 20 && clean_title.length > 20) { title = clean_title.substring(0, 17) + '...'; }
        else if (clean_title.length > 30) {title = clean_title.substring(0, 27) + '...';}
        // if (event['ticket_purchase_required']) { title = "<img class='event-icon' src='/images/TicketIcon.svg'> " + title; }
        // if (event['foal_recommended']) { title = "<img class='event-icon' src='/images/FoalFriendlyIcon.svg'>" + title; }

        var colorIndex = (event.track == null ? COLORS.length-1 : TRACKS.indexOf(event.track));

        var style = 'left:' + x + 'px;';
        style += 'top:' + y + 'px;';
        style += 'height:' + height + 'px;';
        style += 'width:' + width + 'px;';
        style += 'background:#' + (event.color ? event.color : COLORS[colorIndex]) + ';';
        style += 'border-color:#' + (event.border_color ? event.border_color : BORDER_COLORS[colorIndex]) + ';';
        style += 'color:#' + TextColor(event.color ? event.color : COLORS[colorIndex]) + ';';
        var eventDiv = document.createElement('div');
        eventDiv.className = 'event';
        eventDiv.style.cssText = style;
        eventDiv.innerHTML = "<div>" + customHyphen(title) + "</div>";
        jQuery(eventDiv.children[0]).hyphenate('en-us');
        document.getElementById('mysched').appendChild(eventDiv);
        var descDiv = document.createElement('div');
        descDiv.innerHTML = '<i class="closeEvent"></i><div class="title">'
          + event['title'] + '</div><div class="time"><i class="dashicons dashicons-clock"></i> '
          + "Time: " + formatTime(start_time) + '-' + formatTime(end_time) + '</i></div><div class="room"><i class="dashicons dashicons-location"></i> '
          + "Room: " + room_name + '<br/><br/><div class="desc">'
          + event['description']
          // + (event['ticket_purchase_required'] ? "<br/><br/><img style='float:left;width:2em;margin: 0 2px 2px 0' src='/images/TicketIcon.svg'> A <a href='/registration/tickets'>ticket purchase</a> is required to attend this exclusive event!</a>" : '' )
          // + (event['foal_recommended'] ? "<br/><br/><img style='float:left;width:2em;margin: 0 2px 2px 0' src='/images/FoalFriendlyIcon.svg'> Everfree Northwest recommends this as a foal-friendly event!" : '' )
          + (event['host'] instanceof String && event['host'].length > 0 ? '<br><br>Hosted by ' + event['host'] : '')
          + "</div><br/><br/>";
        var aMySched = document.createElement('a');
        aMySched.href='#';
        aMySched.className = 'addto';
        aMySched.innerHTML = 'Remove from my schedule';
        (function (event) {jQuery(aMySched).click(function() {
          var index = jQuery.inArray(event, myEvents);
          if (index >= 0) {myEvents.splice(index, 1);}
          redrawMySched();
          jQuery(this.parentNode).click();
          return false;
        });})(event);
        descDiv.appendChild(aMySched);
        descDiv.className = 'eventDesc';
        (function(descDiv) {
          jQuery(eventDiv).click(function(e) {
            if (getSelection().toString()) { return false; }
            jQuery('.eventDesc').hide();
            jQuery(descDiv).show();
            var targetCal = jQuery(e.target).closest(".calendar");
            var calOffset = targetCal.offset();
            var offsetX = e.pageX - calOffset.left;
            var offsetY = e.pageY - calOffset.top;
            //offsetX = Math.min(offsetX, targetCal.width() - 300);
            offsetY = Math.min(offsetY, targetCal.height() - descDiv.getBoundingClientRect().height);
            descDiv.style.left = offsetX + 'px';
            descDiv.style.top = offsetY + 'px';
            });
          jQuery(descDiv).click(function() {
            if (getSelection().toString()) { return false; }
            jQuery(descDiv).hide();
          });
        })(descDiv);
        document.getElementById('mysched').appendChild(descDiv);
        processedEvents.push(event);
      }
    }
  }
}