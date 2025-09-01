<div class="d-flex justify-content-end mt-4 mb-2 pr-4">
  <div class="dropdown">
    <button class="bx--btn bx--btn--secondary dropdown-toggle" type="button" id="exportDropdownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Export
    </button>
    <div class="dropdown-menu" aria-labelledby="exportDropdownBtn">
      <a class="dropdown-item" href="#" onclick="exportCSV()">Download CSV</a>
      <a class="dropdown-item" href="#" onclick="exportPNG()">Download PNG</a>
    </div>
  </div>
</div>

<script>
  let cumulativeCSVData = [];
  let plannedActualCSVData = [];

  function exportCSV() {
    const activeId = getActiveContainerId();
    const data = getFieldingCSVData(activeId);
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = activeId + ".csv";
    link.click();
  }

  function exportPNG() {
    const selected = document.querySelector(".bx--content-switcher--selected");
    const targetId = selected?.dataset?.target?.replace("#", "");

    let type = "";
    if (targetId.includes("planned-actual")) {
      type = "fielding-quantities-planned-actual";
    } else if (targetId.includes("cumulative")) {
      type = "fielding-quantities-cumulative";
    }

    const plotEl = document.getElementById(`${type}-line-plot-view`);
    if (!plotEl) return;

    html2canvas(plotEl, {
      backgroundColor: "#ffffff",
      useCORS: true,
      scale: 2
    }).then(canvas => {
      const link = document.createElement("a");
      link.download = `${type}-chart.png`;
      link.href = canvas.toDataURL("image/png");
      link.click();
    });
  }

  function getActiveContainerId() {
    const selected = document.querySelector(".bx--content-switcher--selected");
    return selected?.dataset?.target?.replace("#", "") || "graph";
  }

  function getFieldingCSVData(id) {
    if (id.includes("cumulative")) {
      return cumulativeCSVData;
    } else {
      return plannedActualCSVData;
    }
  }

  function convertToCSV(data) {
    return data.map(row => row.join(",")).join("\n");
  }
</script>

<style>
  .dropdown-menu {
    min-width: 150px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    font-size: 14px;
    z-index: 1050;
  }

  .dropdown-item {
    padding: 8px 16px;
    cursor: pointer;
  }

  .dropdown-item:hover {
    background-color: #e5f0ff;
  }
</style>
