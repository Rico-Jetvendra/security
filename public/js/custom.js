function formatPlate(value) {
    // Keep only letters and numbers
    value = value.toUpperCase().replace(/[^A-Z0-9]/g, '');

    // Split manually with hard limits
    let front  = value.match(/^[A-Z]{0,2}/)?.[0] || '';
    let rest1  = value.slice(front.length);

    let number = rest1.match(/^\d{0,4}/)?.[0] || '';
    let rest2  = rest1.slice(number.length);

    let back   = rest2.match(/^[A-Z]{0,3}/)?.[0] || '';

    let result = front;

    if (number) {
        result += ' ' + number;
    }

    if (back) {
        result += ' ' + back;
    }

    return result;
}

$(document).ready(function () {
    $('.datepicker').each(function () {
        $(this).datepicker({
            uiLibrary: 'bootstrap5',
            format: 'yyyy-mm-dd',
            placeholder: 'yyyy-mm-dd'
        });
    });

    const segment1      = window.location.pathname.split('/').filter(Boolean).at(-1).replace(/-/g, "_");
    if(segment1 === 'public'){
        $.fn.DataTable.ext.pager.numbers_length = 5;

        const table = $('.dataTable').DataTable({
            responsive:true,
            autoWidth:false,
            processing: true,
        });
    }

    $('.npwp-mask').mask('00.000.000.0.000.000');
    $('.ktp-mask').mask('0000000000000000');
    $('.frame-mask').mask('ZZZZZZZZZZZZZZZZZZZZ', {
        translation:{
            'Z': {
                pattern: /[A-Za-z0-9]/
            }
        }
    });
    $('.machine-mask').mask('ZZZZZZZZZZZZZZZZZZZZ', {
        translation:{
            'Z': {
                pattern: /[A-Za-z0-9]/
            }
        }
    });
});

$('#crudModal').on('shown.bs.modal', function () {
    document.querySelectorAll('.searchable-select').forEach((el) => {
        const label = document.querySelector(`label[for="${el.id}"]`);
        const text  = label.innerText.trim().replace('(required)', '');

        if (el.options[0].value === '') {
            el.options[0].text =
                `----- Pilih ${text} -----`;
        }

        if (!el.tomselect) {
            new TomSelect(el, {
                create: false
            });
        }

    });
});
