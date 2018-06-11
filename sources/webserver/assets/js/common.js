function createAlert(type, text)
{
    var allowedTypes = ['danger', 'success', 'warning', 'info', 'primary', 'secondary'];

    var icon = {
        danger: "fa-times-circle",
        success: "fa-check-circle-o",
        warning: "fa-exclamation-triangle",
        info: "fa-info-circle",
        primary: "",
        secondary: ""
    };

    if (allowedTypes.indexOf(type) === -1)
    {
        type = "secondary";
    }

    var alert = '<div class="alert alert-' + type + ' alert-dismissible position-absolute mt-2 fade show" role="alert">\n' +
        '<div class="d-table-cell align-middle px-2">\n' +
        '<span class="fa fa-3x ' + icon[type] + ' text-' + type + '"></span>\n' +
        '</div>\n' +
        '<div class="d-table-cell align-middle px-2">\n' +
        '<p class="mb-0">' + text + '</p>\n' +
        '</div>\n' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
        '<span aria-hidden="true">&times;</span>\n' +
        '</button>' +
        '</div>';

    return alert;
}

function parseResult(data)
{
    switch(data.status)
    {
        case 202:
            $("#email_ask").modal("hide");
            $("body").prepend(createAlert("success", data.message));
            break;
        case 400:
        case 404:
            $("#email").val(null);
            $("#error-hint").html(data.message);
            $("#email").val(null).addClass("is-invalid");
            break;
        default:
            $("#email_ask").modal("hide");
            $("body").prepend(createAlert("danger", data.message));
    }
    if ($(".alert").length > 0)
    {
        setTimeout(function() {
            $(".alert").alert('close');
        }, 5000)
    }

}

fetch('config.json')
    .then(response => response.json())
    .then(settings => {

        $("#request_pass").click(function () {
            var email = $("#email").val();
            if (email.length === 0)
            {
                $("#email").val(null).addClass("is-invalid");
                $("#error-hint").text("Please enter a valid email address!");
                return;
            }
            $.ajax({
                url: settings.protocol + "://" + settings.producer + "/index.php",
                dataType: "jsonp",
                data: {
                    "authToken": settings.authToken,
                    "payload": {"email": email},
                    "action": "resetPassword"
                }
            })
        });
    });

$(function()
{
    $("#email_ask").on('show.bs.modal', function() {
        $("#email").val(null);
        $("#error-hint").text(null);
        if ($("#email").hasClass("is-invalid"))
        {
            $("#email").removeClass("is-invalid");
        }
        if ($(".alert").length > 0)
        {
            $(".alert").alert('close');
        }
    });

    $("#email").on("focus", function() {
        $("#error-hint").text(null);
        if ($("#email").hasClass("is-invalid"))
        {
            $("#email").removeClass("is-invalid");
        }
    });

    $("#submit").on("focus", function() {
        if ($("#username").val().length > 0 && $("#password").val().length > 0)
        {
            $("#login_form").removeAttr("onsubmit");
        }
        else
        {
            $("body").prepend(createAlert("danger", "Username and password are required."));
        }
    });
});
