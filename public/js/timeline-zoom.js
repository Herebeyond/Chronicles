// Timeline Zoom Controller
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('timeline-track');
    if (!track) return;
    
    const viewport = document.querySelector('.timeline-viewport');
    const zoomLevelDisplay = document.querySelector('.zoom-level');
    const segments = track.querySelectorAll('.timeline-segment');
    
    if (segments.length === 0) {
        console.log('No timeline segments found');
        return;
    }
    
    let currentZoom = 1.0;
    const zoomStep = 0.5;
    const minZoom = 0.5;
    const maxZoom = 10.0;
    
    let eventsData = [];
    let minYear = Infinity;
    let maxYear = -Infinity;
    
    // Parse all events data
    segments.forEach((segment, index) => {
        // Parse date handling negative years
        const parseDate = (dateStr) => {
            // Match pattern: optional minus, year, month, day
            const match = dateStr.match(/^(-?\d+)-(\d+)-(\d+)$/);
            if (!match) return null;
            return {
                year: parseInt(match[1]),
                month: parseInt(match[2]),
                day: parseInt(match[3])
            };
        };
        
        const startDate = parseDate(segment.dataset.start);
        if (!startDate) {
            console.error('Invalid start date:', segment.dataset.start);
            return;
        }
        
        const startYear = startDate.year;
        const startMonth = startDate.month;
        const startDay = startDate.day;
        
        let endYear, endMonth, endDay;
        const isOngoing = segment.dataset.end === 'ongoing';
        
        if (isOngoing) {
            // For ongoing events, extend far into future
            endYear = startYear + 200;
            endMonth = 12;
            endDay = 30;
        } else {
            const endDate = parseDate(segment.dataset.end);
            if (!endDate) {
                console.error('Invalid end date:', segment.dataset.end);
                return;
            }
            endYear = endDate.year;
            endMonth = endDate.month;
            endDay = endDate.day;
        }
        
        minYear = Math.min(minYear, startYear);
        maxYear = Math.max(maxYear, endYear);
        
        eventsData.push({
            segment,
            startYear,
            startMonth,
            startDay,
            endYear,
            endMonth,
            endDay,
            isOngoing
        });
    });
    
    console.log(`Timeline range: ${minYear} to ${maxYear} (${maxYear - minYear} years)`);
    console.log(`Found ${eventsData.length} events`);
    
    // Calculate row assignment for each event to avoid overlaps
    function assignRows() {
        const rows = []; // Array of arrays, each containing events on that row
        
        eventsData.forEach(event => {
            // Calculate day values for this event
            const startDays = (event.startYear - minYear) * 360 + (event.startMonth - 1) * 30 + event.startDay;
            const endDays = (event.endYear - minYear) * 360 + (event.endMonth - 1) * 30 + event.endDay;
            event.startDays = startDays;
            event.endDays = endDays;
            
            // Find the first row where this event fits (no overlap)
            let assignedRow = -1;
            for (let rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                let hasOverlap = false;
                
                // Check if event overlaps with any event on this row
                for (const existingEvent of rows[rowIndex]) {
                    // Events overlap if: A.start < B.end AND B.start < A.end
                    if (startDays < existingEvent.endDays && endDays > existingEvent.startDays) {
                        hasOverlap = true;
                        break;
                    }
                }
                
                if (!hasOverlap) {
                    assignedRow = rowIndex;
                    break;
                }
            }
            
            // If no existing row works, create a new row
            if (assignedRow === -1) {
                assignedRow = rows.length;
                rows.push([]);
            }
            
            // Assign event to the row
            event.row = assignedRow;
            rows[assignedRow].push(event);
        });
        
        return rows.length; // Return total number of rows
    }
    
    // Calculate timeline layout
    function calculateLayout() {
        const yearSpan = maxYear - minYear;
        const totalDays = yearSpan * 360; // 12 months * 30 days per year
        
        // Assign rows to events
        const totalRows = assignRows();
        
        // Set base track width (in pixels)
        const baseWidthPerYear = 2; // pixels per year at 100% zoom
        const baseTrackWidth = Math.max(1000, yearSpan * baseWidthPerYear); // Minimum 1000px
        const actualTrackWidth = baseTrackWidth * currentZoom;
        
        // Set track dimensions
        const rowHeight = 50; // Height per row in pixels
        const rowSpacing = 10; // Spacing between rows
        const trackHeight = totalRows * (rowHeight + rowSpacing) + 20; // Add padding
        
        track.style.width = actualTrackWidth + 'px';
        track.style.height = trackHeight + 'px';
        
        console.log(`Track: ${actualTrackWidth}px × ${trackHeight}px (${yearSpan} years, ${totalRows} rows)`);
        
        // Position each segment
        eventsData.forEach((event, index) => {
            const startDays = event.startDays;
            const endDays = event.endDays;
            const durationDays = endDays - startDays;
            
            // Calculate pixel positions
            const startPosition = (startDays / totalDays) * actualTrackWidth;
            const width = Math.max(50, (durationDays / totalDays) * actualTrackWidth);
            
            // Calculate vertical position based on row
            const topPosition = event.row * (rowHeight + rowSpacing) + 10;
            
            event.segment.style.left = startPosition + 'px';
            event.segment.style.width = width + 'px';
            event.segment.style.top = topPosition + 'px';
            
            if (index === 0) {
                console.log(`Event 0: row=${event.row}, top=${topPosition}px, left=${startPosition}px, width=${width}px`);
            }
        });
    }
    
    // Zoom functions
    function updateZoom(newZoom) {
        currentZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
        zoomLevelDisplay.textContent = Math.round(currentZoom * 100) + '%';
        calculateLayout();
    }
    
    // Event listeners for zoom buttons
    document.querySelectorAll('.zoom-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            
            switch(action) {
                case 'zoom-in':
                    updateZoom(currentZoom + zoomStep);
                    break;
                case 'zoom-out':
                    updateZoom(currentZoom - zoomStep);
                    break;
                case 'zoom-reset':
                    updateZoom(1.0);
                    break;
            }
        });
    });
    
    // Initial layout
    calculateLayout();
});
