$(function () {
  function showError($el, msg) {
    $el.addClass("invalid");
    $el.closest(".field").find(".error").text(msg);
  }

  function clearErrors() {
    $(".invalid").removeClass("invalid");
    $(".error").text("");
  }

  // Client-side validation (jQuery)
  function validate() {
    clearErrors();
    let ok = true;

    const name = $("#fullname").val().trim();
    if (name.length < 3) { showError($("#fullname"), "Enter your full name (min 3 characters)."); ok = false; }

    const email = $("#email").val().trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError($("#email"), "Enter a valid email."); ok = false; }

    const phone = $("#phone").val().trim();
    if (!/^[6-9]\d{9}$/.test(phone)) { showError($("#phone"), "Enter a valid 10-digit Indian mobile number."); ok = false; }

    if (!$("#dob").val()) { showError($("#dob"), "Select your date of birth."); ok = false; }

    if (!$("input[name=gender]:checked").length) {
      $("input[name=gender]").closest(".field").find(".error").text("Select a gender."); ok = false;
    }

    if (!$("#course").val()) { showError($("#course"), "Select a course."); ok = false; }

    if ($("#address").val().trim().length < 10) { showError($("#address"), "Enter a complete address."); ok = false; }

    return ok;
  }

  function escapeHtml(s) {
    return $("<div>").text(s).html();
  }

  function renderResult(d) {
    const interests = d.interests.length
      ? d.interests.map(i => `<span class="badge">${escapeHtml(i)}</span>`).join("")
      : "—";

    const html = `
      <h1>Registration Successful</h1>
      <div class="success-banner">Thank you, ${escapeHtml(d.fullname)}! Your registration ID is ${escapeHtml(d.reg_id)}.</div>
      <table class="result-table">
        <tr><th>Full Name</th><td>${escapeHtml(d.fullname)}</td></tr>
        <tr><th>Email</th><td>${escapeHtml(d.email)}</td></tr>
        <tr><th>Phone</th><td>${escapeHtml(d.phone)}</td></tr>
        <tr><th>Date of Birth</th><td>${escapeHtml(d.dob)}</td></tr>
        <tr><th>Gender</th><td>${escapeHtml(d.gender)}</td></tr>
        <tr><th>Course</th><td>${escapeHtml(d.course)}</td></tr>
        <tr><th>Interests</th><td>${interests}</td></tr>
        <tr><th>Address</th><td>${escapeHtml(d.address).replace(/\n/g, "<br>")}</td></tr>
        <tr><th>Submitted At</th><td>${escapeHtml(d.submitted_at)}</td></tr>
      </table>
      <button id="printBtn">Print</button>
      <button id="backBtn" class="secondary">New Registration</button>
    `;
    $("#formCard").addClass("hidden");
    $("#resultCard").html(html).removeClass("hidden");
  }

  $("#regForm").on("submit", function (e) {
    e.preventDefault();
    if (!validate()) return;

    const $btn = $("#submitBtn").prop("disabled", true).text("Submitting...");

    $.ajax({
      url: "submit.php",
      method: "POST",
      data: $(this).serialize(),
      dataType: "json"
    })
      .done(function (res) {
        if (res.success) {
          renderResult(res.data);
        } else {
          clearErrors();
          $.each(res.errors || {}, function (field, msg) {
            showError($("[name='" + field + "']").first(), msg);
          });
        }
      })
      .fail(function () {
        alert("Could not reach the server. Please try again.");
      })
      .always(function () {
        $btn.prop("disabled", false).text("Submit");
      });
  });

  $(document).on("click", "#printBtn", function () { window.print(); });

  $(document).on("click", "#backBtn", function () {
    $("#regForm")[0].reset();
    clearErrors();
    $("#resultCard").addClass("hidden").empty();
    $("#formCard").removeClass("hidden");
  });
});
