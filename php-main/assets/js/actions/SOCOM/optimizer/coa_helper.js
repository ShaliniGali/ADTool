function covertToProgramId(type_of_coa, params, is_hash=true)
{
    let program_id = '';
    let program_code, pom_sponsor, cap_sponsor, ass_area_code, 
        execution_manager, resource_category, eoc_code, osd_pe_code, event_name = '';
    switch (type_of_coa) {
        case 'ISS':
        case 'RC_T':
            program_code = params['program_code'] ?? '';
            pom_sponsor = params['pom_sponsor'] ?? '';
            cap_sponsor = params['cap_sponsor'] ?? '';
            ass_area_code = params['ass_area_code'] ?? '';
            execution_manager = params['execution_manager'] ?? '';
            resource_category = params['resource_category'] ?? '';
            eoc_code = params['eoc_code'] ?? '';
            osd_pe_code = params['osd_pe_code'] ?? '';

            program_id = [
                program_code, pom_sponsor, cap_sponsor, ass_area_code, 
                execution_manager, resource_category, eoc_code, osd_pe_code
            ].join('_');
            break;
        case 'ISS_EXTRACT':
            program_code = params['program_code'] ?? '';
            pom_sponsor = params['pom_sponsor'] ?? '';
            cap_sponsor = params['cap_sponsor'] ?? '';
            ass_area_code = params['ass_area_code'] ?? '';
            execution_manager = params['execution_manager'] ?? '';
            resource_category = params['resource_category'] ?? '';
            eoc_code = params['eoc_code'] ?? '';
            osd_pe_code = params['osd_pe_code'] ?? '';
            event_name = params['event_name'] ?? '';

            program_id = [
                program_code, pom_sponsor, cap_sponsor, ass_area_code, 
                execution_manager, resource_category, eoc_code, osd_pe_code, event_name
            ].join('_');
            break;
        default:
            program_id = '';
            break;
          
    }

    return is_hash ? CryptoJS.SHA512(program_id).toString(CryptoJS.enc.Hex) : program_id;
}