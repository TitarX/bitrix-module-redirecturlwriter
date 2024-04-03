'use strict';

window.addEventListener('load', function () {
    document.getElementById('open_file_dialog_button').onclick = OpenFileDialog;

    document.getElementById('start-work-button').addEventListener('click', function () {
        BX.adjust(BX('work-info'), {html: ''});
        const requestedPage = document.getElementById('requested-page').value.trim();
        const waitSpinner = BX.showWait('work-info-spinner');
        prepareWork(requestedPage, waitSpinner);
    });
});

function selectedFilePath(filename, path, site) {
    let csvPath = `${path}/${filename}`;

    let matchResult = csvPath.match(/.+\.csv$/);
    if (!matchResult) {
        if (csvPath.charAt(csvPath.length - 1) !== '/') {
            csvPath += '/';
        }

        csvPath += 'upload_urls.csv';
    }

    jQuery('#selected_file_path').val(csvPath);
}

function prepareWork(url, waitSpinner) {
    const filepath = document.getElementById('selected_file_path').value.trim();

    const params = {
        filepath: filepath
    }

    fetch(`${url}?action=checkfileexists`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(
        response => response.json()
    ).then(
        (data) => {
            if (data.result && data.result === 'yes') {
                saveParams(url, params, waitSpinner);
            } else {
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_FILE_MISS', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
            BX.closeWait('work-info-spinner', waitSpinner);
        }
    );
}

function saveParams(url, params, waitSpinner) {
    fetch(`${url}?action=saveparams`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(
        response => response.json()
    ).then(
        (data) => {
            if (data.result === 'fail') {
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PARAMS_ERROR', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            } else {
                collateUrl(url, params, waitSpinner);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
            BX.closeWait('work-info-spinner', waitSpinner);
        }
    );
}

function collateUrl(url, params, waitSpinner) {
    fetch(`${url}?action=collateurl`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(
        response => response.json()
    ).then(
        (data) => {
            if (data.result === 'xlsxparseerror') {
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PARSEXLSX_ERROR', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            } else if (data.result === 'filenotfound') {
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_XLSXNOTFOUND_ERROR', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            } else if (data.result === 'writeoldurlserror') {
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_WRITEOLDURLSERROR_ERROR', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            } else {
                showMessage(url, 'OK', 'DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_SUCCESS', {}, 'work-info');

                if (data.result.products_without_old_urls_file_path) {
                    let productWithoutOldUrl = jQuery('#product-without-old-url');
                    productWithoutOldUrl.empty();

                    jQuery('<a href="' + data.result.products_without_old_urls_file_path + '" download>'
                        + BX.message('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PROD_URLS_FILE')
                        + '</a>').appendTo(productWithoutOldUrl);
                }

                if (data.result.sections_without_old_urls_file_path) {
                    let sectionWithoutOldUrl = jQuery('#section-without-old-url');
                    sectionWithoutOldUrl.empty();

                    jQuery('<a href="' + data.result.sections_without_old_urls_file_path + '" download>'
                        + BX.message('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_SECT_URLS_FILE')
                        + '</a>').appendTo(sectionWithoutOldUrl);
                }

                if (data.result.bad_urls_file_path) {
                    let badUrl = jQuery('#bad-url');
                    badUrl.empty();

                    jQuery('<a href="' + data.result.bad_urls_file_path + '" download>'
                        + BX.message('DIGITMIND_REDIRECTURLWRITER_BAD_URLS_FILE')
                        + '</a>').appendTo(badUrl);
                }

                BX.closeWait('work-info-spinner', waitSpinner);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
            BX.closeWait('work-info-spinner', waitSpinner);
        }
    );
}
