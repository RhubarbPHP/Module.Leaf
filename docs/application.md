Application Components
======================

An Application Leaf is a building block for building common user interface elements in a 
web based application.

They are designed to inter-operate with each other in a loosely coupled way, augmenting each other
by detecting and handling events as appropriate.

For example a `Table` component will display a paged list of records, but by connecting it with
a `SearchPanel` component that list can now be filtered by user inputs. Connect the `SearchPanel`
component to a `SearchPanelTabs` component and now the search values can be changed by selecting
tabs.

