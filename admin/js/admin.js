jQuery(document).ready(function($) {
    $("#elr-toggle-debug-log").on("click", function() {
        $("#elr-debug-log-content").toggle();
        $(this).text(function(i, text) {
            return text === "Display debug.log contents" ? "Hide debug.log contents" : "Display debug.log contents";
        });
    });
     // Clear the debug.log file
     $("#elr-clear-debug-log").on("click", function() {
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "elr_clear_debug_log",
                nonce: elr_vars.nonce
            },
            success: function() {
                $("#elr-debug-log-content").text("");
                $("#elr-notice").html('<span style="color:green;">Debug log file has been cleared.</span>').fadeIn().delay(3000).fadeOut();
            },
            error: function() {
                $("#elr-notice").html('<span style="color:red;">Error: Unable to clear the debug.log file.</span>').fadeIn().delay(3000).fadeOut();
            }
        });
    });

 // Send debug.log contents to ChatGPT and display the output
 $("#elr-tell-me-whats-wrong").on("click", function() {
    const debugContent = $("#elr-debug-log-content").text();
    const prompt = "Please tell me what is wrong with my WordPress website. Here is my debug.log file: " + debugContent;

    $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
            action: "elr_send_to_chatgpt",
            nonce: elr_vars.chatgpt_nonce,
            prompt: prompt
        },
        beforeSend: function() {
            $("#elr-chatgpt-output").text("Analyzing your debug.log, please wait...").show();
        },
        success: function(response) {
            $("#elr-chatgpt-output").text(response.data);
        },
        error: function() {
            $("#elr-chatgpt-output").text("Error: Unable to get a response from ChatGPT.");
        }
    });
});

// Show the "Tell me what's wrong with my site" button when debug.log content is displayed
$("#elr-toggle-debug-log").on("click", function() {
    $("#elr-tell-me-whats-wrong").toggle();
});
});


