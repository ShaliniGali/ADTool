**# SOCOM v1.6.0 Release Notes**
### Release Date: 07/25/2025

## Infrastructure / Security
### RBAC User Groups
- "User" Group:
-- J8/J8-A users able to view all submitted Excel template submissions (and any Phase 4 edits/changes) from Cap Sponsors<br>
-- J8/J8-A users with admin permissions able to "Approve" Event submission to update data displayed throughout app

### "Restricted User" & "Guest" Group
- Group users able to submit their own ZBTs/Issues via Excel template0706
- Group users able to view Events submitted by themselves and other members belonging to their Cap Sponsor group
- Group users able to submit proposed Events to POM Admins for Approval into application
- Group users not able to view submissions from other Cap Sponsors

## Data/Architecture
- Updated data to include latest/final iteration of Cap Sponsor submitted ZBTs and Issues
- Updated LOOKUP_PROGRAM to reflect latest ZBTs/Issues
- Updated VARCHAR length on a number of fields in table schemas to support all possible PPBES-MIS output values
- Incorporated new tables to support Phase 3 and Phase 4 of Data Upload

## Software
### Data Management/Data Upload
- Fixed bug where string value "N/A" was read in as NULL
- Addressed table activation not bringing in specific columns that contain a "/" in the template

- (UPDATE) Phase 1 of Data Upload "In POM Data Upload"
-- Split Errors into two types - "Errors" prevent table activation while "Warning" provides visibility of possible data integrity problems<br>
-- Updated VARCHAR length for a number of fields in schema to support all possible PPBES-MIS output values

- (NEW) Phase 2 of Data Upload "Out of POM Data Upload"
-- Allows POM Admins to upload locked PB/ENT/ACT positions from PPBES-MIS Export for a chosen year<br>
-- Includes logic checks to ensure correct schema and app-necessary fields are populated: Data Validation

- (NEW) Phase 3 of Data Upload "ZBT/Issues Template"
-- Grants users outside of "User" group ability to upload and submit ZBT/Issues by Excel template ("wide" data in form of PPBES-MIS ZBT/Issue Report) directly to app<br>
-- ZBT/Issues submitted are only visible to other users (except "User" - J8/J8-A) belonging to that Cap Sponsor<br>
-- ZBT/Issues visible through new Table Viewer/Editor functionality which displays Events<br>
-- Cap Sponsor users can submit their uploaded Events for Admin approval<br>
-- POM Admins can view and approve Events submitted by Cap Sponsors

- (BETA) Phase 4 of Data Upload "ZBT/Issue in UI"
-- Grants Cap Sponsors the ability to make edits to their Excel template submitted Events in the new Table Viewer/Editor<br>
-- Functionality enables customization and Cap Sponsor collaboration with innate conflict resolution (with merges) and version control

- (NEW) Errors/Warnings
-- Errors include funding line not appropriately covered in LOOKUP_PROGRAM, missing columns, and unexpected Fiscal Years<br>
-- Warnings include negative Resource $K values and exceeding VARCHAR limit for data fields not displayed in application

### Portfolio Viewer
- Updated front-end visual UI in Budget Trends, Program Drill-down, and Compare Programs

### ZBT/ISS Summary
- Fixed bug where multiple PEOs for SOF AT&L would cause misalignments between Cap Sponsor and Events on "Events by Capability (Approve/Reject)" graph

### Optimizer
- Updated Program Alignment/Optimizer wireframe for Issue Optimization to correctly display LOOKUP_PROGRAM column PROGRAM_ID
- Updated Optimizer algorithm input for Issue Optimization to utilize LOOKUP_PROGRAM column PROGRAM_ID
- Updated Program Alignment Export/Data Upload Import for Issue Optimization to correctly use LOOKUP_PROGRAM column PROGRAM_ID
- Fixed Business Rule (Remove from Play/Priority) for Resource Constraining Optimization

#### Manual Override
- Fixed use of "trashcan", Add EOC, sorting, and search bar filter to correctly reset rows when using "gears" functionality

#### Detailed COA Summary/Comparison
- "Exclude" $K in EOC Code/Resource Category for Issue Optimization Detailed COA Summary now shows correct $K
- Updated "Exclude" for Issue Optimization Detailed COA Summary EOC Code/Resource Category to correctly show for StoRM as well as weighted runs
- Updated "Exclude" for Issue Optimization Detailed COA Summary JCA, KOP/KSP, and CGA to show values from table data rather than solely Optimizer input

**# SOCOM v1.5.2 Release Notes**
### Release Date: 07/07/2025

## Infrastructure / Security
### RBAC User Groups
- "User" Group:
-- Removed SOLIC association - group now contains solely J8 and J8-A
- "Restricted User" Group:
-- Added SOLIC association alongside SOF AT&L and SOFM<br>
-- Restricted access to app expanded to additionally exclude Create COA, COA Management, and Import Data subapps<br>
-- Added restricted access for viewing Release Notes<br>
- "Guest" Group:
- Added restricted access for viewing Release Notes

## Data
- Updated data to include 26PB lock (PB Comparison, Budget to Execution, Portfolio Viewer subapps)
- Updated data to include 27EXT lock and updated 27ZBT and 27ISS interim data to reflect 27EXT starting position
- Updated schema for LOOKUP_PROGRAM table as well as for DT tables (EXT, ZBT_EXTRACT, ZBT, ISS_EXTRACT, ISS, POM) to support code refactoring

## Software
### (NEW) Data Management
- Phase 1 of Data Upload "In POM Data Upload"
-- Allows POM Admin users to upload locked EXT/ZBT/ISS/POM positions from PPBES-MIS Export<br>
-- Includes logic checks to ensure correct schema and app-necessary fields are populated (Data Validation)

### PB Comparison
- PB Comparison now dynamically updates to support new PB positions (and includes 26PB)

### Portfolio Viewer
- Program Execution Drill Down top graph now correctly accounts for addition of 26PB showing 25PB for FY2025 and 26PB for FY2026 through FY2030

### Optimizer
- Updated endpoints to support schema changes/code refactoring
- Issue Optimization optimized inputs now additionally grouped by EOC Code, Appropriation, OSD PE Code, and Execution Manager
- Fixed visual bug on Optimizer tab display that wouldn't show updates to "Remaining $K" and "Excess Cuts $K" for Issue Optimization and Resource Constraining, respectively as a result of Manual Override (correctly displays in Manual Override or when reloading COA)

### Detailed COA Summary/Comparison
- Updated endpoints to support schema changes/code refactoring
- Fixed error with how $K over FYDP are allocated in Detailed COA Summary/Comparison for IO/RC allowing Partial Years in EOC Code/Resource Category tab for funding lines that have a gap in funding for one or more years over FYDP (pre-Manual Override and post-Manual Override)

### Manual Override
- Updated endpoints to support schema changes/code refactoring
- Applied fix for reset "gear" functionality in Manual Override for Resource Constraining to reset to original value from DT if Optimizer recommended that it be cut

**# SOCOM v1.5.1 Release Notes**
### Release Date: 06/13/2025

## Infrastructure / Security
### [NEW] RBAC (Role-Based Access Controls)
- RBAC is to manage user access to resources, granting permissions based on their roles within an organization. 
-- Instead of assigning individual permissions to each user, RBAC groups users into roles, and then permissions are assigned to those roles. <br>
-- This simplifies access management, improves security, and ensures compliance

### RBAC User Groups
- "User" Group:
-- Full access to app (excluding Admin/POM Admin only controls)<br>
-- Able to be designated an AO or an AD<br>
-- Able to see display for AO/AD Comments & Recommendations and leave comments/recommendations<br>
-- Able to become an Admin/POM Admin<br>
-- Associated by Cap Sponsor J8, J8-A, or SOLIC

- "Restricted User" Group:
-- Restricted access to app<br>
-- Unable to see or otherwise access AO/AD Comments & Recommendations<br>
-- Unable to utilize "Review Status" column filter or view associated column in ZBT/ISS Overall Event Summary<br>
-- Cannot become an Admin or POM Admin<br>
-- Associated by Cap Sponsor belonging to SOF AT&L or SOFM

- "Guest" Group:
-- Most restrictive app use<br>
-- Unable to see or otherwise access AO/AD Comments & Recommendations<br>
-- Unable to utilize "Review Status" column filter or view associated column in ZBT/ISS Overall Event Summary<br>
-- Unable to view outside of their own their own Cap Sponsor designation for PB Comparison, Budget to Execution, and Portfolio Viewer subapps<br>
-- Unable to access Create COA, COA Management, or Import Data subapps<br>
-- Cannot become an Admin or POM Admin<br>
-- Associated by Cap Sponsor if not "User" or "Restricted User"

### MySQL database configuration
- Updated architecture to extend python/MySQL database connection timeout threshold

## Software
###  Create COA
### Detailed COA Summary/Comparison
- Removed 0 $K FYDP programs from being added via "Add EOC" for Resource Constraining/Issue Optimization Manual Override
- Added OSD PE field for Issue Optimization/Resource Constraining Detailed COA Summary/Comparison
- Detailed COA Comparison for Resource Constraining now compares what is to be cut rather than what it is to be funded for all tabs
- Removed 0 FYDP lines from "Exclude" in Detailed COA Summary EOC Code/Resource Category tab for Resource Constraining
- Detailed COA Summary tree maps for JCA, KOP/KSP, and Capability Gaps appropriately account for partially removed programs for Resource Constraining
- Addressed issue in Detailed COA Comparison for Resource Constraining and Detailed COA Summary/Comparison for Issue Optimization where $K could be misaligned by FY

### Manual Override
- Added Execution Manager column title in Issue Optimization Manual Override Export
- "Add EOC" in Resource Constraining Manual Override now contains fields for OSD PE and Execution Manager
- "Add EOC" in Issue Optimization Manual Override now contains fields for Execution Manager
- Fixed niche case interaction with "gear" scaling reset and "Add EOC"/delete funding line for Issue Optimization/Resource Constraining Manual Override
- Fixed niche case interaction with "gear" scaling reset and sorting by column/filtering by selection for Issue Optimization/Resource Constraining Manual Override
- Fixed calculation of Proposed Budget $K/Uncommitted $K in Resource Constraining for Manual Override to account for Remove from Play and 100% cut Tranche programs
- Fixed rare bug where "Add EOC" in Issue Optimization/Resource Constraining Manual Override could allow for the addition of funding lines that were already present in the COA

### Optimizer
- Addressed issue that could cause Run Optimizer button to be greyed out when working in Optimizer
- Adjusted visual placement of custom weights within Optimizer/Program Alignment

### COA Management
- Merge COA for Resource Constraining appropriately accounts for OSD PE and Execution Manager

### Portfolio Viewer
- Added OSD PE column for "Compare Programs" subtab in Portfolio Viewer
- Fixed instances in Portfolio Viewer where visuals would load in a sporadic manner

**# SOCOM v1.5.0 Release Notes**
### Release Date: 05/16/2025

## Data
- Further updates to reflect current state of Issues and ZBTs for POM27

## Software
### Create COA
### Resource Constraining Optimizer (w/ Budget Cuts and Tranches)
- Updated "Exclude" sub-tab in Detailed COA Summary for Resource Constraining Manual Override to include cut amounts for partially cut programs
- Updated Tranche display dropdown selection visual to better form-fit space when evaluating an individual tranche
- Addressed consistency with selections of EOC Codes for "Add EOC" in Issue Optimization/Resource Constraining Manual Override
- 3rd level details for JCA Alignment now properly display when toggled
- Resource Constraining Merge COA now always selects $K from a given COA, even if it was partially cut
- Addressed display issue for RDTE $ for Issue Optimization/Resource Constraining in EOC Code/Resource Category tab
- Fixed rare issue in Detailed COA Summary where $K could be misaligned by FY

### Import Data - Program Alignment
- Fixed custom weight imports for Issue Optimization and Resource Constraining Program Alignment

### Portfolio Viewer
- Added "toggle filter" for "Budget Trends" and "Compare Programs" to allow for enabling/disabling view of all PB funding lines
- Added Resource Category filter for "Compare Programs" graphs and table
- Added disclaimer related to nature of displayed Financial Execution Module data in "Program Execution Drill-down"

**# SOCOM v1.4.9 Release Notes**
### Release Date: 05/07/2025

## Data
- Updated onsite data to support Consolidated Issues for POM27
- Updated onsite data to support Working (WKG) position from PPBES-MIS pulled May 6th 2027, which reflects SO-PDM I

## Software
### Program Alignment
- Added new column "Resource Category Code" for RC Optimizer in the Program Alignment

### Create COA
### Resource Constraining Optimizer (w/ Budget Cuts and Tranches)
- Added new column "Resource Category Code" in the RC Optimizer Table View to display Detailed breakdown of Programs
- Enabled Detailed COA Summary/Comparison for Resource Constraining
- Enabled Share COA for Resource Constraining
- Enabled Merge COA for Resource Constraining
- Enabled Export Scoring Templates and Import POM/Guidance Scores for Resource Constraining
- Incorporated "Tranche breakdown" filter in Optimizer output visualization 
- Added "History" view for the Business Rules applied to Must-include/exclude programs in optimization and Added Reset Functionality
- Fixed issue where some funding lines were not passed on appropriately to Optimizer/Manual Override for Resource Constraining
- Addressed "gear" functionality for Manual Override for Resource Constraining not resetting to full 100% originally requested value from DT table if it was cut by the algorithm
- Fixed bug affecting visual display from "Advanced" when changing the number of Tranches while "Advanced" tab is open

### Issue Optimization
- Fixed bug with "gear" functionality for lines in Manual Override that have the same Program Code, Cap Sponsor, and/or EOC Code but differ for other fields for Issue Optimization

**# SOCOM v1.4.8 Release Notes**
### Release Date: 04/25/2025

## Database
- Included updated LOOKUP_PROGRAM table to support reworked Resource Constraining Optimization

## Software
### Create COA:
### [New] Resource Constraining Optimizer (w/ Budget Cuts and Tranches)
- Allows COA creation by cutting from existing Portfolio
-- Can cut based on a given input % or by specifying $ per FY in the FYDP
- Introduced "Business Rules" - which allows users to Prioritize/Remove From Play based on filter selections
-- Filter selections for "Business Rules" are any combination of Program Group, Resource Category Code, and Capability Sponsor Code
- Users can choose between 1 and 4 tranches to divide Programs into
-- "Advanced" tab allows users to specify whether to fully fund high scoring Programs after enough $K has been cut has been cut for that FY, or keep cutting
- If keep cutting is enabled, Programs marked "Priority" will not be cut ("salami slice")
-- Default values are supplied for how Tranches are utilized dependent on number of Tranches chosen
- Users can specify how much to cut in percentage terms from each Tranche
- Users can specify how much in percentage terms needs to come from each Tranche of the $K cut 
- Fixed "gear" functionality in Manual Override for funding lines that have identical Program Code/Cap Sponsor but differs among other
- Fixed bug where FYDP value in Create COA Optimizer would show FYDP as "NaN" if there was a missing year in the FYDP for that Program
- Addressed bug in Manual Override concerning FYDP $K for Programs with more than one Capability Sponsor
- Fixed niche case of Detailed COA Summary EOC Code/Resource Category tab incorrectly associating $K to FY

### ZBT/ISS Summary
- Incorporated Program Breakdown for ZBT/ISS Summary
-- Given Capability Sponsor, Assessment Area, and Program Group filters display associated changes by filter selection for ZBT/ISS
- ZBT Program Breakdown has three stacked bar charts that display positive changes, negative changes, and sum of total changes
- ZBT Program Breakdown has an additional filter that allows users to toggle between all submitted ZBTs or just approved ZBTs
- ISS Program Breakdown has two stacked bar charts that display positive changes and sum of total changes
- ISS Program Breakdown has an additional filter that allows users to toggle between all submitted Issues, all submitted ZBTs/Issues, approved ZBTs/all Issues, and approved ZBTs/Issues
- ZBT/ISS Historical POM graph extended back two years to show full FYDP for current position in the POM cycle as well as previous two POMs (for POM27 Cycle, extend back to 2025 for POM25)
- Individual Issues/ZBTs within ZBT/ISS Overall Event Summary will now display "Review Status" for that respective Event
- Navigating through ZBT/ISS Overall Event Summary will retain memory of filter selections when traversing between Overall view and individual Events
- Clicking on pie chart/graphical elements in ZBT/ISS Summary will take users to Overall Event Summary with active filters depending on what was clicked
- Fixed display window for "gear" functionality in Approve at Scale in ZBT/ISS Overall Event Summary
- Fixed ZBT Overall Event Summary to show more than one Capability Sponsor for Events that have more than one Capability Sponsor (CROSS ZBTs)

### Portfolio Viewer 
- Added disclaimer in Portfolio Viewer Budgeting Trends that all $ amounts are in $K (just like rest of the application, to match PPBES-MIS)
- In "Fielding" tab of Program Execution Drill Down, removed years from selection drop-down that do not have any quantities with type of "Fielding"

**# SOCOM v1.4.7 Release Notes**
### Release Date: 04/04/2025

## Infrastructure
- Added permission system restricting visibility of PMO app upon user registration, with viewing access granted by Admin (for GAIA only users and future functionality)

## Database
- Ingested updated DT_AMS_MILESTONE table to include additional data related to Requirements

## Software
### Portfolio Viewer 
- Re-organized layout of Portfolio Viewer main page to allow for users to directly enter Program Execution Drill Down view
- Added Assessment Area filter for Portfolio Viewer main page, granting users a high-level view of SOF AT&L Programs filtered by Assessment Area
- Updated visual chart for Financial Execution in Program Execution Drill Down to display a "combo graph"- single line with two bar charts for each year instead of three lines
- Updated color scheme for new Financial Execution
- Updated chart for Fielding tab of Program Execution Drill Down to only show Fielding (non-Delivery/Funding) Actual/Planned Quantities as "Actual vs Planned Qty"
- Added separate sub-tab for Fielding Program Execution to show cumulative planned Funding, Delivery, and Fielding Quantities as "Cumulative Fielding Qty"
- Added additional functionality for Milestone in Program Execution Drill Down to display further detail on Requirements associated with a given Milestone on click

### PB Comparisons / Budget to Execution
- Added a "range slider" for Fiscal Year in PB Comparison and Budget to Execution subapp that allows customers to dynamically set viewed years

### ZBT/ISS Summary
- Added top level filter for Assessment Area Code for ZBT/ISS Overall Event Summary that allows users to display Events containing funding lines by specific Assessment Area(s)
- Added additional filter "Review Status" to ZBT Overall Event Summary marking ZBTs as having no AO/AD comment/recommendation, at least one "Disapprove", or having no "Disapprove"
- Added additional filter "Review Status" to ISS Overall Event Summary marking Issues as having no AO/AD comment/recommendation and flags for having at least one "Disapprove", "Approve", or "Approve at Scale"
- Added EOC Code filter to ZBT/ISS Historical POM and EOC Details to allow filtering of tables/graphs by EOC Codes, defaults to ALL
- Added search bar, FYDP column, and expanded pagination options for EOC/Resource Category tab of Detailed COA Summary/Comparison in Create COA
- Addressed bug for users with solely "AO" permission leaving comments/recommendations on ZBTs/Issues
- Fixed some minor visual bugs with "gear" functionality scaling funding rows for Approve at Scale in ZBT/ISS Overall Event Summary and Manual Override in Create COA
- Fixed minor text bug for AD Final Action in ZBT/ISS Overall Event Summary
- Capability Sponsor column in ZBT Overall Event Summary now displays all Cap Sponsors for CROSS Events
- Increased size of text/text boxes in ZBT/ISS Overall Event Summary display

### Create COA:
### Optimizer
- Added FYDP column in wireframe display for Program Alignment/Optimizer subpages in Create COA subapp
- Updated "Visual" view in JCA, KOP/KSP, and CGA tab in Detailed COA Summary/Comparison to have total FYDP displayed for Treemap as well as % of total FYDP per segment, on hover
- Fixed "Data" view in JCA, KOP/KSP, and CGA tab within Detailed COA Summary/Comparison for Optimizer in Create COA to show Capability Sponsor rather than POM Sponsor
- Users can now appropriately access User Dashboard from Optimizer/Program Alignment subpages in Create COA subapp
- Fixed issue using "gear" functionality in Manual Override on Program lines added via "Add EOC" that were not originally selected by Optimizer

### Build Weights and Scores
- Implemented rounding for Program scores for POM/Guidance scoring

**# SOCOM v1.4.6 Release Notes**
### Release Date: 03/18/2025

## Database
- Curated SOF AT&L Execution/Programmatic data (currently AMS)
- Ingested four new tables for Portfolio Viewer: LOOKUP_AMS_PGMX, DT_AMS_FEM, DT_AMS_MILESTONE, and DT_AMS_FIELDING
-- Tables comprise detailed descriptive information for AMS Programs, Financial Execution Module data, Milestone data, and Fielding data
- Extended yearly coverage for DT_PB_COMPARISON and DT_BUDGET_EXECUTION data tables back to 2016, previously went as far back as 2020

## Software
### [New Feature] Portfolio Viewer 
- Linked PPBES-MIS data to SOF AT&L Execution/Programmatic data (currently AMS)

### Budget Trend Overview
- Top-level view of Program Group (PPBES-MIS) with filters by Capability Sponsor/Component and Resource Category- and Program Group
- Line graph of PB FYDP as well as Enacted/Execution lines given filter from 2016 to 2024 (with PB FYDP extending from 2025 to 2029)
-- PB FYDP/Enacted/Execution lines adjustable to show "then-year $" vs "current-year $'
- Horizontal stacked bar-chart of top 10 Program Groups in terms of $ for latest PB FYDP
- Breaks down given filter selections to show horizontal stacked bar-chart of latest PB FYDP by Resource Category
- Linked to Program Execution Drilldown

### Program Execution Drilldown
- Users can click on a Program Group to enter drill-down with expanded detail
- Shows Program Descriptions, Program Issues, and Program Accomplishments for each SOF AT&L Program linked to Program Group
- Funding line graph of Planned Amount, Obligated Amount, and Expenditure Amount from SOF AT&L data related to linked Program Group
- Line graph of Execution data for Program Group until 2024 and latest PB FYDP from 2025 to 2029
- Table view of Previous, Current, and Future Milestones (and any dates) associated with each SOF AT&L Program
- Displaying Planned/Actual quantities of Fielding Items via Program Group with drop-down filters for year/component
- Displaying Fielding Item to show line graph depicting detail related to that item over time

### Compare Programs
- Users can compare up to two Program Groups (Compare Programs)
- Top left and top right quadrants of page show PB FYDP/Enacted/Execution liens of Program Groups side-by-side
- Bottom left quadrant shows horizontal stacked bar-chart of selected Programs with Execution from 2016 up to 2024 and latest PB FYDP from 2025 to 2029
- Bottom right quadrant shows comparative table view of Plan/Obligated/Expenditure Amount (and additional info) breaking down selected Programs by EOC, Resource Category, and Fiscal Year

### Create COA:
### Optimizer
- Updated Optimizer page in Resource Constrained COA to include more form-fitting colored bar chart and various other visual improvements
- Added "gear" icon functionality to Manual Override for Issue Optimization/Resource Constraining COAs which allows users to scale an entire row of funding by a %
- "gear" functionality in Manual Override allows user to reset funding lines from scaled % back to original

### ZBT/ISS Summary
- Incorporate "gear" functionality into "Approve at Scale" for ZBT/ISS Event Summary with "Final AD Action"
- Added "running tally" to ISS Overall Event Summary that shows total committed $ from "Approve" or "Approve at Scale", allowing users to easily track what they've approved
- ADs can now reset "AD Final Action" in ISS Overall Event Summary for added flexibility
- Introduced Overall Event Summary to ZBT Summary - includes main overview page and "Approve at Scale" functionality
- Updated bottom right chart in ZBT Summary to source Event Status from user tables, pending their entries
- Fixed minor bug with some additional rows being shown in ZBT EOC Details within ZBT Summary

**# SOCOM v1.4.5 Release Notes**
### Release Date: 02/28/2025

## Infrastructure
- Incorporated REDIS functionality for ZBT/ISS Program Summary interface

## Software
### ZBT/ISS Summary
- Bottom right chart in ISS Summary now correctly pulls based off AD Final Actions
- "Granted"/"Proposed" radio button for ISS Summary Overall Event Summary now shows originally proposed $$ for "Disapproved" programs

### PB Comparisons / Budget to Execution
- Expanded filter options (added Execution Manager, Program Code, OSD Program Element Code, and EOC Code) for PB Comparison
- Expanded filter options (added Execution Manager, Program Code, OSD Program Element Code, and EOC Code) for Budget to Execution
- Changed position line for PB2025 in PB Comparison subapp to be a dotted line to reflect that there is no Enacted or Actuals position for FY25

### Create COA:
### Optimizer
- Changed default "Score Type" from "Score per $" to "Score" in Optimizer
- Sorting in Manual Override for Issue Optimization or Resource Constraining COAs now correctly always keeps "Grand Total" row as last
- Fixed bug in "Issue Analysis" for Detailed COA Summary/Comparison not handling "Capability Sponsor Request" lines appropriately for niche cases
- Users are now able to add more than one EOC Code from a Program with "Add EOC" feature in Manual Override for Issue Optimization/Resource Constraining COAs
- Fixed bug which prevented Issue Analysis from functioning correctly for Issue Optimization COAs
- After switching to "Resource Constraining" mode in Optimizer or Program Alignment, maintains selection through app unless changed
- Fixed bug with Manual Override and Detailed COA Summary/Comparison for Merged COAs
- Fixed bug with Manual Override and Detailed COA Summary/Comparison for COAs with an added or deleted EOC

### Build Weights and Scores
- Updated Weights Builder front-end and added ability to search by Weight Criteria
- Introduced feature allowing admins to set, edit, and delete descriptive information relating to custom Weighting Criteria in a given cycle

### Program Alignment
- Hovering over Weighting Criteria in Program Alignment will show the associated description
- After switching to "Resource Constraining" mode in Optimizer or Program Alignment, maintains selection through app unless changed

### COA Management
- Merge COA for Issue Optimization COAs is now appropriately broken out by Event Name

**# SOCOM v1.4.4 Release Notes**
### Release Date: 02/17/2025

## Infrastructure
- Implemented new feature to allow admin access to MySQL Container Pod
- Increased default timeout limit from 10 minutes to 60 minutes
## Data
- Program ID schema updates for the Issue Optimization: EVENT_NAME in POM_SPONSOR_CODE
- Issue Optimization to show Event Name as well as Program
- Curated Data tables for PB Comparison and Budget to Execution for Data load efficiency 

## Software
### ZBT/ISS Summary
- Added "Proposed/Granted" button to Overall Event Summary for Issue Summary
- Added pagination options for Overall Event Summary for Issue Summary
- Provided dark yellow color to "Not Decided" events in Overall Event Summary
- Fixed ISS EOC Summary to appropriately break out Events out by EOC Code, Resource Category, and OSD PE Code
- Fixed bug with certain long ZBT/ISS Event Justifications not expanding correctly with "View More"

### PB Comparisons / Budget to Execution
- Updated drop-down filter for PB Comparison and Budget to Execution to use Program Group instead of Program Code (pending expanded graph filter functionality)

### Resource Constrained COA -> Create COA: Feature Name Change
### Program Alignment
- Renamed "Programs w/ Weight and Score Values" subpage in "Create COA" to "Program Alignment"
- Updated "Program Alignment" and "Optimizer" wireframes for Issue Optimization to show Event Name as well as Program
- Incorporated fixes to enable users to import Program Alignments from an Excel template
- Fixed discrepancy between Program Alignment/Optimizer Programs displayed and Programs exported

### Optimizer
- Enabled Share COA feature for Issue Optimization COAs
- Enabled Merge COA feature for Issue Optimization COAs
- Added ability to sort columns in Manual Override for Issue Optimization and Resource Constraining
- Updated "Program Alignment" and "Optimizer" wireframes for Issue Optimization to show Event Name as well as Program
- Updated data to break out Programs present in more than one unique Event for Issue Optimization
- Fixed discrepancy between Program Alignment/Optimizer Programs displayed and Programs exported
- Fixed bug with JCA, KOP/KSP, and CGA Treemaps "Exclude" on Manual Override COAs in Detailed COA Summary
- Issue Analysis in Detailed COA Summary/Comparison now correctly cites Manual Override alterations 

**# SOCOM v1.4.3 Release Notes**
### Release Date: 02/06/2025

## Infrastructure
- Implemented REDIS for caching, session stores, and data structures

## Data
- New PB data to include data for latest PB, PB2025
- New Enacted and Actuals data for latest available year, 2024

## Software
### General
- Added disclaimer to top of main page regarding data used in app from PPBES-MIS

### PB Comparisons / Budget to Execution
- Updated PB Comparison and Budget to Execution subapps to include most recent data

### ZBT/ISS Summary
- Fixed rare bug where users were unable to submit Program Alignments for specific programs
- Fixed bug in ZBT/ISS Program Summary for programs without a JCA Alignment not showing in wireframe
- Updated underlying Export Program Alignment query to match wireframe

### Resource Constrained COA
- Added "Issue Analysis" to Detailed COA Summary/Comparison for Issue Optimization COAs
- Detailed Summary Issue Analysis allows users to view which Issues are included in COA by Program/EOC or by Event
- Detailed Comparison Issue Analysis allows users to compare up to 3 COAs to compare Optimizer Issue funding by Program/EOC or by Event
- Increased maximum number of available Weighted Criteria from 15 to 20
- Renamed Resource Constrained COA to "Create COA" to reflect Issue Optimization and Resource Constraining are both options
- Fixed bug that could cause user to timeout when loading more than one large COA
- Fixed bug with "Add EOC" functionality in Manual Override for Issue Optimization/Resource Constraining COAs
- Added OSD PE Code and Event Name cells to "Add EOC" functionality in Manual Override for Issue Optimization
- Updated column names for Issue Optimization Manual Override export to match other columns
- Fixed issues with Manual Override COAs in Detailed COA Summary/Comparison 

**# SOCOM v1.4.2 Release Notes**
### Release Date: 01/22/2025

## Software
### ZBT/ISS Summary
- Added "Final AD Action" to Issue Summary and Event Summary
- Added "Overall Event Summary" page to Issue Summary
- Added Filters "Issue Capability Sponsor" and "AD Consensus" to Overall Event Summary + Export Option
- Added Dynamic FY$ and FYDP Years based on Final AD Action to Overall Event Summary- Incorporated "Approve by Scale" choice for Ads to "Final AD Action"
- Fixed x-axis on ZBT/ISS Program Summary bottom left stacked bar chart

### Resource Constrained COA- Added Assessment Area Code/Program Group filter to Optimizer page
- Improved current POM position Description
- Improved Optimizer selection:  Issue Optimization ("bills") and Resource Constraining ("offsets")
- Fixed Dynamic POM functionality for
-- Optimizer Type Selection
-- Detailed COA Summary/Comparison
-- Manual Override working
- Fixed data type on Manual Override export to be numeric (for Excel)
- Fixed OSD_PROGRAM_ELEMENT_CODE and EVENT_NAME column headers for Issue Optimization Manual Override being blank for Export
- Fixed StoRM Scores not rendering correctly for Manual Override on some StoRM COAs 

**# SOCOM v1.4.1 Release Notes**
### Release Date: 12/20/2024
 
## Software
### Resource Constrained COA
- Flexible Data Sources (ISS vs. ISS EXTRACT)
-- Added ability to Optimize Issues over DT_ISS_EXTRACT_202X (DELTA AMT) or DT_ISS_202X (RESOURCE K)
-- Customers can now switch between Optimizing Issues and Resource Constraining
-- Incorporated existing COA functionality (Manual Override, Detailed COA Summary/Comparison, Share COA, Merge COA) for Issue Optimization COAs
 
- New Optimizer Metric (Score vs. Score per $)
-- Added ability to include programs via Optimizer from a "Score per $" framework or "Score"
- Added ability to optimize programs with StoRM Score that do not have assigned weighting criteria for POM/Guidance
 
- Bug Fixes
-- Fixed Manual Override auto fill-in for EOC Codes with more than Capability Sponsor or Assessment Area Code
-- Fixed error related to programs with missing StoRM Score in COAs (Merge COA and Manual Override)
 
### ZBT/ISS Summary
- Bug Fixes
-- Fixed displaying Programs in ZBT/ISS Program Summary with a missing JCA Alignment as "0"
-- Fixed queries related to ISS Program Summary causing timeouts
-- Fixed text on ISS EOC Summary chart to correctly reflect changes from "XXZBT to XXISS" rather than "XXEXT to XXZBT"
-- Fixed x-axis ordering in ZBT/ISS Summary for "Dollars Moved by Resource Category" chartdsdsdf vd

**# SOCOM v1.4.0 Release Notes**
### Release Date: 12/01/2024

## Software
### New Feature: Dynamic POM Cycle (Partial)
- Fully incorporated Dynamic POM Cycle feature throughout application
- Moved POM Cycle forward to include POM27 Issue evaluation within ISS Summary subapp
- Introduced POM Center where admins can move POM Cycle forward and view status (present/missing/old) of Tables
- Applied fix for Dynamic POM Years related to ZBT Summary subapp Historical POM and EOC Details queries

### ZBT/ISS Summary
- Added Event Summary view for ISS Summary

**# SOCOM v1.3.9 Release Notes**
### Release Date: 11/15/2024

## Software
### New Feature: Dynamic POM Cycle
- Added Dynamic POM Year/Position functionality to queries, updating subapps to current POM Year and Position within POM Cycle
- Admin users have visibility on latest available tables for phases in POM Cycle and ability to progress forward in POM Cycle
 
### ZBT/ISS Summary
- Updated back-end Program Lookup Table incorporating new Programs/EOCs and to include multiple Assessment Areas
- Updated ZBT/ISS Summary Approve/Reject Bar Chart by Cap Sponsor to group together SOF AT&L PEO Elements
- Removed POM Sponsor Code field visibility within ZBT/ISS EOC Details
- Implemented performance optimizations related to transitioning back and forth between Event Summary/Program Summary
- Added EVENT Summary for ISS 
 
- Adjusted Out of Balance Warning Threshold (added in v.1.3.8)
- Fixed Data table warning in EVENT SUMMARY > EOC CODE -> Historical POM (added in v.1.3.8)

### Resource Constrained COA
- Fixed instances of Non-covered/Covered overlap within KOP/KSP data tab within Detailed COA Summary/Comparison
- Removed redundant column "Resource K" within data tab for JCA, KOP/KSP, CGA for Detailed COA Summary/Comparison
- Fix for Import/Export functionality for mapping Program Alignment
- Fixed Treemap functionality for JCA, KOP/KSP, CGA for Detailed COA Summary/Comparison on Merged COAs

**# SOCOM v1.3.8 Release Notes**
### Release Date: 11/01/2024
 
## Software
### ZBT Summary
- Added Event Summary tab for ZBTs subapp under ZBT Summary
-- Event Summary allows users to view funding lines by (Program/EOC) by Event
-- Event Summary displays relevant information for funding lines, Event-relevant information, and warning if ZBT out of balance
--- Event Name, Event Justification (up to 200 characters + opening modal if longer), Delta $, AO/AD Details, Warning for ZBT out of balance
--- Dropdown to go to other EVENTS
-- Added linkage between ZBT Program Summary and ZBT Event Summary allowing users to move through views
--- Link back to Program Summary with "EOC Code"
 
- Updated API queries in ZBT Summary to support data for new POM cycle
-- A. ZBT Summary Page FY Columns 2027, 2028, 2029, 2030, 2031
-- B. ZBT Program Summary Page FY Columns 2027, 2028, 2029, 2030, 2031
-- C. EOC Summary Page FY Columns 2027, 2028, 2029, 2030, 2031
--- now showing 27EXT, 27ZBT REQUESTED, 27ZBT REQUESTED DELTA
--- Updated Graph to have 27EXT and 27ZBT Requested
-- D. EVENT Summary Page FY Columns 2027, 2028, 2029, 2030, 2031
-- E. ZBT Program Historical POM Page FY Columns 2027, 2028, 2029, 2030, 2031
--- Historical POM Graph to have 2027 to 2031 with 25POM, 26POM, 27ZBT Requested
--- Current POM graph to have 2027 to 2031 with 27EXT and 27ZBT Requested
--- Removed Rows before 25EXT: FY 24*
--- Removed Rows 26ZBT REQUESTED and 26ZBT REQUESTED DELTA
--- Added Rows after 26EXT: 26ZBT -> 26ZBT DELTA -> 26ISS -> 26ISS DELTA -> 26POM -> 26EXT TO 26POM DELTA -> 27EXT -> 27ZBT REQUESTED -> 27ZBT REQUESTED DELTA
 
## Data
- Integrated three new DTs - DT_EXT_2027 (interim 27BES as 27EXT is not finalized), DT_ZBT_EXTRACT_2027, and DT_POM_2026 in preparation for POM27 ZBT Phase

**# SOCOM v1.3.7 Release Notes**
### Release Date: 10/18/2024
 
## Software
### Resource Constrained COA
- New Feature: Cycle Management
-- Added in functionality for admin users to flexibly change weighting criterium that users consider when evaluating Programs
-- Incorporated functionality of admin users to revert to prior weighting criterium
 
- New Feature: COA Management
-- Customers are now able to share COAs with other users by email
 
- New Feature: Export/Import Weight Scoring
-- Introduced Program Weight Scoring import/export functionality allowing users to score programs en masse
-- Program Weight Scoring Export by Assessment Area Code and Program Group, exports table to Excel
-- Program Weight Scoring Import allows users to bring in scores from exported Excel sheet
 
- New Feature: Merge COA
-- Introduced Merge COA functionality which grants users the ability to create a new COA from two existing COAs
-- Merge COA UI allows users ability to iteratively choose funding lines from either COA to include in new COA
 
### ZBT Summary/ISS Summary
-- Added text expansion functionality for Event Justification in ZBT/ISS EOC Summary to address long text values

**# SOCOM v1.3.6 Release Notes**
### Release Date: 10/08/2024

## Software
### Resource Constrained COA
- Detailed Summary within COA/Detailed Comparison between COAs 
- EOC Code/Resource Category tab shows stacked bar chart breaking down RESOURCE_K $ by FY and Resource Category 
- JCA, KOP/KSP, CGA tab Visual subtab show programs associated with Treemap element upon hover 
- JCA, KOP/KSP, CGA tab Data subtab show RESOURCE_K breakdown by program for strategic item 
- Manual Override and Detailed COA Summary/Comparison now fully integrated, showing manual changes across Detailed views
### ZBT Summary/ISS Summary
- Program Groups sorted alphabetically for ZBT/ISS Program Summary Program Group filter 
- Updated ZBT/ISS Program Summary queries to hit new JSON oriented JCA LOOKUP TABLE 
- Shortened displayed text and added ellipsis for full display of ZBT/ISS EOC Summary Event Justification (for long text strings) 
- Removed EOC/Program associated from ZBT/ISS AO/AD Recommendation/Comment tables - tying all recommendations/comments solely to Event 
- Removed POM Sponsor filters from ZBT/ISS Summary per POM Sponsor phasing out for FY27 POM Cycle
### PB Comparison/Budget to Execution
- Removed POM Sponsor filters from Budget to Execution, and PB Comparison per POM Sponsor phasing out for FY27 POM Cycle 
- Fixed issue with Budget to Execution/PB Comparison Resource Category Code filter being inactive

**# SOCOM v1.3.5 Release Notes**
### Release Date: 09/10/2024
 
## Software
### Resource Constrained COA
- Completed Detailed COA Comparison/Summary + Bug Fixes
-- KOP/KSP tab Visual subtab displays Treemaps of KOPs/KSPs RESOURCE_K coverage by $K amount
-- KOP/KSP tab Data subtab shows breakdown of KOPs/KSPs Covered/Non-covered
-- JCA Alignment tab Data subtab correctly shows all Non-covered JCA Alignments appropriately
-- Capability Gap tab Data subtab correctly shows all Non-covered Capability Gaps appropriately
-- JCA Alignment tab with "3rd level detail" selected now matches colors across comparisons
-- JCA Alignment tab Visual subtab filters lower level options correctly based on higher level options
-- Capability Gap tab Visual subtab filters lower level options correctly based on higher level options
 
- Manual Override
-- Manual Override now displays EOC Codes with more than one associated Resource Category Code correctly with full funding
-- Manual Override cells colored blue now correctly show for partial years and not all 0 funding years
 
### ZBT Summary/ISS Summary
- ZBT/ISS EOC Details Graph now displays correct values in instances of more than one Event per EOC Code
 
### PB Comparison/Budget to Execution
- PB Comparison Resource Category Code selection now filters down from above selections POM Sponsor/Cap Sponsor/Assessment Area Code/Program
- Budget to Execution Resource Category Code selection now filters down from above selections POM Sponsor/Cap Sponsor/Assessment Area Code/Program

**# SOCOM v1.3.4 Release Notes**
### Release Date: 09/04/2024

## Software

### Resource Constrained COA
- Added "JCA Alignment" and "Capability Gaps" tab to Detailed COA Comparison/Summary -- JCA Alignment tab Visual subtab displays Treemaps of JCA Alignment RESOURCE_K coverage by $K amount -- JCA Alignment tab Data subtab shows breakdown of JCA Alignments Covered/Non-covered -- Allows user to filter examining down to 2nd level or 3rd level of JCA Alignments -- Capability Gaps tab Visual subtab displays Treemaps of Capability Gaps RESOURCE_K coverage by $K amount -- Capability Gaps tab Data subtab shows breakdown of Capability Gaps Covered/Non-covered
-- Fixed Inventory displaying as a Resource Category in Resource Constrained COA subapp (Manual Override and Detailed COA Comparison/Summary) -- Fixed error where EOC Codes with more than one Resource Category would bring in funds for only one of the categories -- Fixed some cells in Manual Override being colored blue as though were partially funded programs when they were not -- Standardized color schemes for EOC Code/Resource Category bar charts of Detailed COA Comparison/Summary feature -- Fixed issue where "Detailed Summary" and "Detailed Comparison" button lingered on page after user selected "Create new COA"

### ZBT Summary/ISS Summary
- Fixed issue where selecting "ALL" Program Group for ZBT/ISS Summary returned 0 for all metrics in top right corner box
- Show Capabilty/POM Sponsor acronyms rather than full meaning in ZBT Summary/ISS Summary/PB Comparison/Budget to Execution subapps

### PB Comparison/Budget to Execution
- Fixed PB Comparison/Budget to Execution subapps not showing data when partially missing funding data
- Fixed Inventory displaying as a Resource Category in PB Comparison/Budget to Execution subapps
- Show Capability/POM Sponsor acronyms rather than full meaning in ZBT Summary/ISS Summary/PB Comparison/Budget to Execution subapps -- Show Resource Category code acronyms rather than full meaning in PB Comparison/Budget to Execution subapp

**# SOCOM v1.3.3 Release Notes**
### Release Date: 08/21/2024

## Infrastructure
### Azure IL5/IL6, SIPR
- Configured all of the prerequisite networks and Azure portals
- Created Bastion Host, Admin VM and Base network to begin the installation
- Configured Nexus registry that contains the OS-level and application library packages
- Installed Gitlab and Velero
- Deployed containers to be loaded to SIPR
- Implemented SMTP access to enable email verification and send notifications
- Registered domain name for Guardian apps deployed at socom - guardian.socom.smil.mil
- Established SOCOM database connections
-- PPBES-MIS, JCA Alignment (J8), J8S STORM 2.0 SURVEY PROCESSING, CAPABILITY GAPS (J59)
- Configured Kubernetes and Argocd to automate deployments

## Software
### ZBT Summary
- Displaying ZBT statistics including Number of ZBTs, Dollars Moved, Net Change
-- Added Piechart for Number of ZBTs and Dollars by Capability Sponsors
-- Added Time-series Line chart for Dollars Moved by Resource Categories
-- Added Barchart for Number of ZBTs by Capability Sponsors by Approved/Rejected

- ZBT Program Summary
-- Displaying a table view of Programs with EOC, JCA Alignment by POM positions along with FYDP $ and Approval Action Status
-- 26EXT, 26ZBT Requested, 26ZBT Requested Delta in Positions
-- Added Four Filters including Capability Sponsors, Assessment Area Code, POM Sponsor, Program Group
-- Added Approval Status Filter: Completed and Pending
-- Added a Filter for Zero FYDP Funding Program List

- ZBT Program Historical POM 
-- Displaying table view for selected program along with JCA Alignment by POM positions
-- 24EXT, 24ZBT, 24ZBT Delta, 24ISS, 24ISS Delta, 24POM, 24EXT to 24POM Delta
-- 25EXT, 25ZBT, 25ZBT Delta, 25ISS, 25ISS Delta, 25POM, 25EXT to 25POM Delta
-- 26EXT, 26ZBT Requested, 26ZBT Requested Delta
-- Graph view for Current POM Cycle and Historical POM

- ZBT EOC Details
-- Displaying table view for selected program along with EOC, Event Name, Assessment Area, POM/capability Sponsors, Resource Category, Event Justification by POM positions
-- Displaying AO Recommendations with cross-user visibility
-- Displaying AD Approvals with cross-user visibility
-- Displaying AO/AD Comment with cross-user visibility

### ISS Summary
- Displaying ISS statistics including Number of ISSs, Dollars Moved, Net Change
-- Added Piechart for Number of ISSs and Dollars by Capability Sponsors
-- Added Time-series Line chart for Dollars Moved by Resource Categories
-- Added Barchart for Number of ISSs by Capability Sponsors by Approved/Rejected

- ISS Program Summary
-- Displaying a table view of Programs with EOC, JCA Alignment by POM positions along with FYDP $ and Approval Action Status
-- 26EXT, 26ZBT, 26ZBT Delta, 26ISS Requested, 26ISS Requested Delta in Positions
-- Added Four Filters including Capability Sponsors, Assessment Area Code, POM Sponsor, Program Group
-- Added Approval Status Filter: Completed and Pending
-- Added a Filter for Zero FYDP Funding Program List

- ISS Program Historical POM 
-- Displaying table view for selected program along with JCA Alignment by POM positions
-- 24EXT, 24ZBT, 24ZBT Delta, 24ISS, 24ISS Delta, 24POM, 24EXT to 24POM Delta
-- 25EXT, 25ZBT, 25ZBT Delta, 25ISS, 25ISS Delta, 25POM, 25EXT to 25POM Delta
-- 26EXT, 26ZBT, 26ZBT Detla, 26ISS Requested, 26ISS Requested Delta
-- Graph view for Current POM Cycle and Historical POM

- ISS EOC Details
-- Displaying table view for selected program along with EOC, Event Name, Assessment Area, POM/capability Sponsors, Resource Category, Event Justification by POM positions
-- Displaying AO Recommendations with cross-user visibility
-- Displaying AD Approvals with cross-user visibility
-- Displaying AO/AD Comment with cross-user visibility

### PB Comparison
- Displaying graph view of PB Dollars from FY2020 to Future FYs
-- Added Five Filters: Capability Sponsor, Assessment Area Code, POM Sponsor, Program, and Resource Category
-- Added "Compare" Functionality to allow users comparing different set of filters by Splitting view into two columns. Two Graph Views. 

### Budget to Execution
- Displaying graph view of President's Budget, Execution, and Enacted Dollars from FY2020 to Current FY
-- Added Five Filters: Capability Sponsor, Assessment Area Code, POM Sponsor, Program, and Resource Category
-- Added "Compare" Functionality to allow users comparing different set of filters by Splitting view into two columns. Two Graph Views. 

### Resource Constrained COA
- Programs with Weight and Score Values
-- Radio buttons to select Weighted vs StoRM 
-- StoRM
--- Loaded StoRM Scores/IDs by Programs
--- List of Programs with POM/Capability Sponsors and Resource K FYDP Dollars
-- POM/Guidance Weights
--- Link to connect Weight Builder
--- Link to list Weights List
--- Dropdown to show Available Weights
--- Displaying Guidance and POM Weights
--- List of Programs with POM/Capability Sponsors and Resource K FYDP Dollars
--- Button to "Add Score" by Programs
--- Users can add/edit Scores by Weights and save Score title and Description

- Build Weights and Scores
-- Users can edit Weights for Guidance/POM criteria
-- Users can view StoRM Scores by IDs

- Saved Weights and Scores
-- Users can view the saved Weight Criteria List

- Optimizer
-- Radio buttons to select Weighted vs StoRM 
-- StoRM
--- List of Programs with POM/Capability Sponsors and StoRM Scores
--- Priority (Must Include) and Remove From Play (Must Exclude) options for the optimizer
--- Option to consider All FYDP Years Delta $ by Program to allow or disallow inclusion of partial year programs
--- Users to enter Proposed Budget $
--- Run Optimizer to select Programs
--- Displaying Remaining $ With Respect to Proposed Budget
--- Save/Load/Create New COA
--- Displaying barchart to show optimizer selected Programs and earned total scores

-- POM/Guidance Weights
--- List of Programs with POM/Capability Sponsors and POM/Guidance Scores
--- Priority (Must Include) and Remove From Play (Must Exclude) options for the optimizer
--- Option to consider All FYDP Years Delta $ by Program
--- Users to enter Proposed Budget $
--- Run Optimizer to select Programs
--- Displaying Remaining $ With Respect to Proposed Budget
--- Save/Load/Create New COA
--- Link to connect Weight Builder
--- Link to list Weights List
--- Dropdown to show Available Weights
--- Displaying Guidance and POM Weights
--- Displaying barchart to show optimizer selected Programs and earned total scores

-- COA Comparison
--- Load Multiple COAs to compare different Optimizer Runs

-- Manual Override
--- COA Manual Override to add, delete, edit the selected programs
--- Can alter at the EOC Code level
--- Recalculating Total Committed $ and Uncommitted $ with respect to proposed Budget
--- Color coding for Manual Override Indicators: Green - Valid, Red - Invalid, Yellow - Edited, Blue - Partial. 
--- Saving User's Manual Override and Justifications
--- Toggle to Show Original Optimizer Outputs
--- Download Optimizer Results and Manual Overrides

-- Detailed Summary
--- Detailed Summary to compare Selected vs Not-selected Programs by the Optimizer
--- Showing EOC/Resource Category Details
--- Barchart to show EOC/Resource Category $
--- Filters by FY and Capability Sponsors
--- Table view to compare Selected vs Not-selected Programs by the Optimizer

-- Detailed Comparison
--- Detailed Comparison for up to three COAs
--- Showing EOC/Resource Category Details
--- Barchart to show EOC/Resource Category $
--- Filters by FY and Capability Sponsors
--- Table view to compare up to Three COAs by the Optimizer