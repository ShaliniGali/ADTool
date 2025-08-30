function getNotes() {
    loadPageData('#release-notes-main', 'release_notes/get_note', function(){
        
        let select2Data = [];
        versions.forEach((v, i) => {
            select2Data.push({
                id: i * 2,
                text: v
            })
        })

        $('#release-notes-select').select2({
            width: 'resolve',
            data: select2Data,
            placeholder: "Select version",
        }).on("change", function(){
            changeVersion();
        });
    })
}

function changeVersion(){
    let versionId = $('#release-notes-select').val();
    $('html, body').animate({
        scrollTop: $("#head-"+versionId).offset().top - 20
    }, 1000);
}

if (!window._rb) window._rb = {}
window._rb.getNotes = getNotes;
window._rb.changeVersion = changeVersion;