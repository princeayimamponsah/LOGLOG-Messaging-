function showForm(formId){
    document.querySelectorAll(".form-box").forEach(form => {
        form.classList.add("hidden");
        form.classList.remove("active");
    });
    const targetForm = document.getElementById(formId);
    if (targetForm) {
        targetForm.classList.remove("hidden");
        targetForm.classList.add("active");
    }
}

document.addEventListener("click", function (event) {
    const trigger = event.target.closest("[data-show-form]");
    if (!trigger) return;

    event.preventDefault();
    const formId = trigger.getAttribute("data-show-form");
    if (formId) showForm(formId);
});

window.showForm = showForm;
