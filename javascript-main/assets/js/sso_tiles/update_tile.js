"use strict"

function populate_fields(tile_name){
    $.post("/kc_tiles/populate_fields", { tile_name: tile_name, rhombus_token: rhombuscookie() }, function (data, status) {
        data = JSON.parse(data);
        if(data.length>0){
            $('#description-text').val(data[0].description);
            $('#svg-text').val(data[0].icon);
            $('#label-text').val(data[0].label);
            $('#update-tile').prop('disabled',false);
        }
    })
}

function save_tiles(){
    let title = $('#select-tile').val();
    let svg = $('#svg-text').val();
    let label = $('#label-text').val();
    let description = $('#description-text').val();

    if(svg=='' || label=='' || description==''){
        return;
    }

    $.post("/kc_tiles/save_tiles", { title: title, svg:svg,label:label,description:description, rhombus_token: rhombuscookie() }, function (data, status) {
        if(data){
            console.log('success');
        }
    })

}

if (!window._rb) window._rb = {
    save_tiles: save_tiles,
    populate_fields: populate_fields
}