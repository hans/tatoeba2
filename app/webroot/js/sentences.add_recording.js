/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009  HO Ngoc Phuong Trang <tranglich@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


$(document).ready(function() {

  $(".recordLink").click(function() {
    var sentenceId = $(this).data("sentenceId");
    var withAudio = $(this).data("withAudio");

    var rootUrl = get_tatoeba_root_url();

    // Initialize recorder
    Recorder.initialize({
      swfSrc: '/swf/recorder.swf'
    });

    /*
     * Save translation.
     */
    function save() {
      var sentenceText = $("#_" + sentenceId + "_text").val();
      var selectLang = $("#translationLang_" + sentenceId).val();

      if ($.trim(sentenceText) != "") {
        unbind(); // very important
        // This unbind() applies for the submit button and input field.
        sentenceText = normalized_sentence(sentenceText);

        $("#_" + sentenceId + "_translations").show();
        $("#_" + sentenceId + "_loading").show();
        $(".addTranslations").hide();

        $.post(
        rootUrl + "/sentences/save_translation", {
          "id": sentenceId,
          "selectLang": selectLang,
          "value": sentenceText,
          "parentOwnerName": parentOwnerName,
          "withAudio": withAudio
        }, function(data) {
          $("#session_expired").remove();
          $("#_" + sentenceId + "_loading").hide();
          $("#_" + sentenceId + "_translations").prepend(data);
          $("#_" + sentenceId + "_text").val('');
        }, "html");

      }
    }

    /*
     * Function to unbind the handlers binded to the submit button, input field
     * and cancel button. It is very important to unbind, otherwise a same
     * translation will be save as many times as the user clicked on the
     * "translate" icon.
     */
    function unbind() {
      $("#_" + sentenceId + "_record").unbind('click');
      $("#_" + sentenceId + "_stop_record").unbind('click');
      $("#_" + sentenceId + "_play_recording").unbind('click');
      $("#_" + sentenceId + "_submit_recording").unbind('click');
      $("#_" + sentenceId + "_cancel_recording").unbind('click');
    }

    // Displaying translation input and hiding translations
    $(".sentenceForm, #_" + sentenceId + "_translations").hide();
    $("#recording_for_" + sentenceId).show();

    var statusElement = $('#_' + sentenceId + '_recording_status');

    $('#_' + sentenceId + '_record').click(function() {
      statusElement.text('0:00');

      Recorder.record({
        start: function() {
          statusElement.addClass('active');
        },

        progress: function(ms) {
          var seconds = Math.floor(ms / 1000) % 60;
          var minutes = Math.floor(ms / 1000 / 60);

          var secondsPadded = seconds < 10 ? '0' + seconds : seconds;

          statusElement.text(minutes + ':' + secondsPadded);
        }
      });
    });

    $('#_' + sentenceId + '_stop_record').click(function() {
      statusElement.removeClass('active');

      Recorder.stop();
    });

    $('#_' + sentenceId + '_play_recording').click(function() {
      Recorder.play();
    });

    // Submit recording
    $("#_" + sentenceId + "_submit_recording").click(function() {
      statusElement.removeClass('active');
      Recorder.stop();

      // TODO
    });

    // Cancel
    $("#_" + sentenceId + "_cancel_recording").click(function() {
      statusElement.removeClass('active');
      Recorder.stop();

      unbind(); // very important
      $("#_" + sentenceId + "_translations").show();
      $(".addRecording").hide();
    });
  });

});
