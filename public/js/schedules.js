document.querySelectorAll(".schedule-table").forEach(function(table) {

        // Create the final row containing the plus sign.
        const footer = document.createElement("tfoot");
        const footerRow = document.createElement("tr");
        const addCell = document.createElement("td");

        const columnCount = table.querySelector("thead tr")?.children.length
            || table.querySelector("tbody tr")?.children.length
            || 1;

        addCell.colSpan = columnCount;
        addCell.className = "add-row";
        addCell.textContent = "+";
        addCell.title = "Add another row";

        footerRow.appendChild(addCell);
        footer.appendChild(footerRow);
        table.appendChild(footer);

        // When the plus sign is clicked, add a new row.
        addCell.addEventListener("click", function() {

            const tbody = table.querySelector("tbody");
            const newRow = document.createElement("tr");
            const type = table.dataset.type;

            if (type === "staff" || type === "data") {
                // Keep the first cell as the category label.
                const labelCell = document.createElement("td");
                labelCell.contentEditable = "true";
                labelCell.className = "editable";
                newRow.appendChild(labelCell);

                // Add the remaining blank cells.
                for (let i = 1; i < columnCount; i++) {
                    const cell = document.createElement("td");
                    cell.contentEditable = "true";
                    cell.className = "editable";
                    newRow.appendChild(cell);
                }

            } else if (type === "notes") {
                // Add another dropdown for the Notes table.
                const cell = document.createElement("td");
                const select = document.createElement("select");

                select.className = "form-select notes-select";
                select.innerHTML = `
                    <option value="">Select a note category...</option>
                    <option>General</option>
                    <option>Operations</option>
                    <option>Safety</option>
                    <option>Maintenance</option>
                    <option>Weather</option>
                    <option>Other</option>
                `;

                cell.appendChild(select);
                newRow.appendChild(cell);

            } else {
                // Standard table: create one editable cell per column.
                for (let i = 0; i < columnCount; i++) {
                    const cell = document.createElement("td");
                    cell.contentEditable = "true";
                    cell.className = "editable";
                    newRow.appendChild(cell);
                }
            }

            tbody.appendChild(newRow);
        });
    });