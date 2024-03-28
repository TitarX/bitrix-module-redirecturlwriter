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
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_FILE_MISS', {}, 'work-info');
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
                showMessage(url, 'ERROR', 'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PARAMS_ERROR', {}, 'work-info');
                BX.closeWait('work-info-spinner', waitSpinner);
            } else {
                parseXlsx(url, params, waitSpinner);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
            BX.closeWait('work-info-spinner', waitSpinner);
        }
    );
}

function parseXlsx(url, params, waitSpinner) {
    fetch(`${url}?action=parsexlsx`, {
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
                //
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
            BX.closeWait('work-info-spinner', waitSpinner);
        }
    );
}
