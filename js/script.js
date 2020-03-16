jQuery(document).ready(function () {
    var municipalityClass = jQuery(".user-register-form #edit-municipality .municipality-select");
    municipalitySelect(municipalityClass);
    municipalityClass.change(function () {
        municipalitySelect(this);
    });
});

function municipalitySelect($this) {
    var municipality = jQuery($this).val();
    if (municipality !== '') {
        jQuery($this).parent().parent().find('.form-item-city').addClass('show');
    }
}