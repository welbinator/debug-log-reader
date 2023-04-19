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
            beforeSend: function() {
                $("#elr-clear-debug-log").text("Clearing debug.log file...").prop("disabled", true);
            },
            success: function(response) {
                if (response.success) {
                    $("#elr-debug-log-content").text('');
                    $("#elr-clear-debug-log").text("Clear debug.log file").prop("disabled", false);
                } else {
                    alert("Error: Unable to clear the debug.log file.");
                    $("#elr-clear-debug-log").text("Clear debug.log file").prop("disabled", false);
                }
            },
            error: function() {
                alert("Error: Unable to clear the debug.log file.");
                $("#elr-clear-debug-log").text("Clear debug.log file").prop("disabled", false);
            }
        });
    });
    

    $("#elr-tell-me-whats-wrong").on("click", function() {
        const debugContent = $("#elr-debug-log-content").text();
        const promptIssue = "Please tell me what is wrong with my WordPress website. Please include which plugin or theme is causing the problem and which file and on which line the error is on. Here is my debug.log file: " + debugContent;
        // const promptTroubleshooting = "Also please share some basic WordPress troubleshooting steps, based on my issue.";
    
        // Show the h2 heading immediately after the button is clicked
        $(".chatgpt-output-heading").show();


        function sendChatGPTRequest(prompt, outputSelector, headingSelector) {
            return $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "elr_send_to_chatgpt",
                    nonce: elr_vars.chatgpt_nonce,
                    prompt: prompt
                },
                beforeSend: function() {
                    
                    $(outputSelector).text("Please wait...").show();
                    if (outputSelector === "#elr-chatgpt-output-issue") {
                        $("#elr-chatgpt-output-wrapper").show();
                    }
                },
                success: function(response) {
                    $(outputSelector).html(response.data);
                    $(headingSelector).show(); // Show the heading
        
                    // Show the textarea and submit button after the second output is displayed
                    if (outputSelector === "#elr-chatgpt-output-troubleshooting") {
                        $("#elr-code-input").show();
                        $("label[for='elr-code-input']").show();
                        $("#elr-submit-code").show();
                    }
                },
                error: function() {
                    $(outputSelector).text("Error: Unable to get a response from ChatGPT.");
                }
            });
        }
        

            sendChatGPTRequest(promptIssue, "#elr-chatgpt-output-issue", ".sub-heading")
            .then(function() {
                    $("#elr-code-input").show();
                    $("label[for='elr-code-input']").show();
                    $("#elr-submit-code").show();
            });
    });

    // Add a function to handle the follow-up request
    function sendFollowUpRequest() {
        const followUpPrompt = $("#elr-code-input").val();
        if (!followUpPrompt) {
            return;
        }

        const prompt =  followUpPrompt;

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "elr_send_to_chatgpt",
                nonce: elr_vars.chatgpt_nonce,
                prompt: prompt
            },
            beforeSend: function() {
                $("#elr-chatgpt-output-followup").text("Response incoming...").show();
            },
            success: function(response) {
                $("#elr-chatgpt-output-followup").text(response.data);
            },
            error: function() {
                $("#elr-chatgpt-output-followup").text("Error: Unable to get a response from ChatGPT.");
            }
        });
    }

    $("#elr-submit-code").on("click", sendFollowUpRequest);

    // Show the "Tell me what's wrong with my site" button when debug.log content is displayed
    $("#elr-toggle-debug-log").on("click", function() {
        $("#elr-tell-me-whats-wrong").toggle();
    });
});
