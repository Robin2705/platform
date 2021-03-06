<?php

namespace Oro\Bundle\DataGridBundle\Tests\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\MultipleChoice;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridFilterDateTimeItem;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridFilters;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridFilterStringItem;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Table;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\TableHeader;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridPaginator;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroPageObjectAware;
use Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context\PageObjectDictionary;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class GridContext extends OroFeatureContext implements OroPageObjectAware
{
    use PageObjectDictionary;

    /**
     * @var int
     */
    protected $gridRecordsNumber;

    /**
     * @When I don't select any record from Grid
     */
    public function iDonTSelectAnyRecordFromGrid()
    {
    }

    /**
     * Example: When I click "Delete" link from mass action dropdown
     * Example: And click Delete mass action
     *
     * @When /^(?:|I )click "(?P<title>(?:[^"]|\\")*)" link from mass action dropdown$/
     * @When /^(?:|I )click (?P<title>(?:[^"]|\\")*) mass action$/
     */
    public function clickLinkFromMassActionDropdown($title)
    {
        $grid = $this->getGrid();
        $grid->clickMassActionLink($title);
    }

    /**
     * Example: Then there is one record in grid
     * Example: And there are two records in grid
     * Example: And there are 7 records in grid
     * Example: And number of records should be 34
     *
     * @Given number of records should be :number
     * @Given /^there (are|is) (?P<number>(?:|one|two|\d+)) record(?:|s) in grid$/
     */
    public function numberOfRecordsShouldBe($number)
    {
        self::assertEquals($this->getCount($number), $this->getGridPaginator()->getTotalRecordsCount());
    }

    /**
     * Example: Then number of pages should be 3
     * Example: Then number of pages should be 15
     *
     * @Given number of pages should be :number
     */
    public function numberOfPagesShouldBe($number)
    {
        self::assertEquals((int) $number, $this->getGridPaginator()->getTotalPageCount());
    }

    /**
     * This step used for compare number of records after some actions
     *
     * @Given /^(?:|I )keep in mind number of records in list$/
     */
    public function iKeepInMindNumberOfRecordsInList()
    {
        $this->gridRecordsNumber = $this->getGridPaginator()->getTotalRecordsCount();
    }

    /**
     * Check two records in grid by one step
     * E.g. to check check accounts with "Columbia Pictures" and "Warner Brothers" content in it
     * Example: And check Warner Brothers and Columbia Pictures in grid
     *
     * @Then /^(?:|I )check ([\w\s]*) and ([\w\s]*) in grid$/
     */
    public function checkTwoRecordsInGrid($record1, $record2)
    {
        $this->getGrid()->checkRecord($record1);
        $this->getGrid()->checkRecord($record2);
    }

    /**
     * I select few records == I check first 2 records in grid
     * Example: When I check first 2 records in grid
     * Example: I select few records
     *
     * @When /^(?:|I )check first (?P<number>(?:[^"]|\\")*) records in grid$/
     * @When select few records
     */
    public function iCheckFirstRecordsInGrid($number = 2)
    {
        $this->getGrid()->checkFirstRecords($number);
    }

    /**
     * Checks first records in provided column number
     * Example: And I check first 5 records in 1 column
     *
     * @When /^(?:|I )check first (?P<number>(?:|one|two|\d+)) record(s|) in (?P<column>(?:|one|two|\d+)) column$/
     */
    public function iCheckRecordsInColumn($number, $column)
    {
        $this->getGrid()->checkFirstRecords(
            $this->getCount($number),
            $this->getCount($column)
        );
    }

    /**
     * Unchecks first records in provided column number
     * Example: And I uncheck first 2 records in 1 column
     *
     * @When /^(?:|I )uncheck first (?P<number>(?:[^"]|\\")*) records in (?P<column>(?:[^"]|\\")*) column$/
     */
    public function iUncheckFirstRecordsInColumn($number, $column)
    {
        $this->getGrid()->checkFirstRecords($number, $column);
    }

    /**
     * Example: And I uncheck first 2 records in grid
     *
     * @When /^(?:|I )uncheck first (?P<number>(?:[^"]|\\")*) records in grid$/
     */
    public function iUncheckFirstRecordsInGrid($number)
    {
        $this->getGrid()->checkFirstRecords($number);
    }

    /**
     * Check how much records was deleted after some actions
     * Example: Given go to Customers/ Accounts
     *          And I keep in mind number of records in list
     *          When I check first 2 records in grid
     *          And I click "Delete" link from mass action dropdown
     *          Then the number of records decreased by 2
     *
     * @Then the number of records decreased by :number
     */
    public function theNumberOfRecordsDecreasedBy($number)
    {
        $this->getSession()->getDriver()->waitForAjax();
        self::assertEquals(
            $this->gridRecordsNumber - $number,
            $this->getGridPaginator()->getTotalRecordsCount()
        );
    }

    /**
     * @Then the number of records remained the same
     * @Then no records were deleted
     */
    public function theNumberOfRecordsRemainedTheSame()
    {
        self::assertEquals(
            $this->gridRecordsNumber,
            $this->getGridPaginator()->getTotalRecordsCount()
        );
    }

    /**
     * Example: And I select 10 from per page list dropdown
     *
     * @Given /^(?:|I )select (?P<number>[\d]+) from per page list dropdown$/
     * @Given /^(?:|I )select (?P<number>[\d]+) records per page$/
     */
    public function iSelectFromPerPageListDropdown($number)
    {
        $this->getGrid()->selectPageSize($number);
    }

    /**
     * Proceed forvard oro grid pagination
     *
     * @When /^(?:|I )press next page button$/
     */
    public function iPressNextPageButton()
    {
        $this->getGridPaginator()->clickLink('Next');
    }

    /**
     * Assert number of pages in oro grid
     * It depends on per page and row count values
     * Example: Then number of page should be 3
     *
     * @Then number of page should be :number
     */
    public function numberOfPageShouldBe($number)
    {
        self::assertEquals(
            (int) $number,
            (int) $this->getGridPaginator()->find('css', 'input[type="number"]')->getAttribute('value')
        );
    }

    /**
     * Example: When I fill 4 in page number input
     *
     * @When /^(?:|I )fill (?P<number>[\d]+) in page number input$/
     */
    public function iFillInPageNumberInput($number)
    {
        $this->getGridPaginator()->find('css', 'input[type="number"]')->setValue($number);
    }

    /**
     * Sort grid by column
     * Example: When sort grid by Created at
     * Example: But when I sort grid by First Name again
     *
     * @When /^(?:|when )(?:|I )sort grid by (?P<field>([\w\s]*[^again]))(?:| again)$/
     */
    public function sortGridBy($field)
    {
        $this->elementFactory
            ->createElement('Grid')
            ->getElement('GridHeader')
            ->findElementContains('GridHeaderLink', $field)
            ->click();
    }

    //@codingStandardsIgnoreStart
    /**
     * @Then /^(?P<column>([\w\s]+)) in (?P<rowNumber1>(first|second|[\d]+)) row must be (?P<comparison>(lower|greater|equal)) then in (?P<rowNumber2>(first|second|[\d]+)) row$/
     */
    //@codingStandardsIgnoreEnd
    public function compareRowValues($column, $comparison, $rowNumber1, $rowNumber2)
    {
        $rowNumber1 = $this->getNumberFromString($rowNumber1);
        $rowNumber2 = $this->getNumberFromString($rowNumber2);

        $value1 = $this->getGrid()->getRowByNumber($rowNumber1)->getCellValue($column);
        $value2 = $this->getGrid()->getRowByNumber($rowNumber2)->getCellValue($column);

        switch ($comparison) {
            case 'lower':
                self::assertGreaterThan($value1, $value2);
                break;
            case 'greater':
                self::assertLessThan($value1, $value2);
                break;
            case 'equal':
                self::assertEquals($value1, $value2);
                break;
        }
    }

    /**
     * Assert column values by given row
     * Example: Then I should see Charlie Sheen in grid with following data:
     *            | Email   | charlie@gmail.com   |
     *            | Phone   | +1 415-731-9375     |
     *            | Country | Ukraine             |
     *            | State   | Kharkivs'ka Oblast' |
     *
     * @Then /^(?:|I )should see (?P<content>([\w\s\.\_]+)) in grid with following data:$/
     */
    public function assertRowValues($content, TableNode $table)
    {
        /** @var Grid $grid */
        $grid = $this->elementFactory->findElementContains('Grid', $content);

        /** @var TableHeader $gridHeader */
        $gridHeader = $grid->getElement('GridHeader');
        $row = $grid->getRowByContent($content);

        $crawler = new Crawler($row->getHtml());
        /** @var Crawler[] $columns */
        $columns = $crawler->filter('td')->siblings()->each(function (Crawler $td) {
            return $td;
        });

        foreach ($table->getRows() as list($header, $value)) {
            $columnNumber = $gridHeader->getColumnNumber($header);
            $actualValue = trim($columns[$columnNumber-1]->text());

            self::assertEquals(
                $value,
                $actualValue,
                sprintf(
                    'Expect that %s column should be with "%s" value but "%s" found on grid',
                    $header,
                    $value,
                    $actualValue
                )
            );
        }
    }

    /**
     * Assert record position in grid
     * It is find record by text and assert its position
     * Example: Then Zyta Zywiec must be first record
     * Example: And John Doe must be first record
     *
     * @Then /^(?P<content>([\w\s]+)) must be (?P<rowNumber>(first|second|[\d]+)) record$/
     */
    public function assertRowContent($content, $rowNumber)
    {
        $row = $this->getGrid()->getRowByNumber($this->getNumberFromString($rowNumber));
        self::assertRegExp(sprintf('/%s/i', $content), $row->getText());
    }

    /**
     * @Then /^I should see that "(?P<content>([\w\s]+))" is in (?P<rowNum>([\d]+)) row$/
     */
    public function assertRowContentInTable($content, $rowNum)
    {
        /** @var Table $table */
        $table = $this->findElementContains('Table', $content);
        $row = $table->getRowByNumber($rowNum);
        self::assertTrue(
            $row->has('named', ['content', $content]),
            "There is no content '$content' in $rowNum row"
        );
    }

    /**
     * Assert that text is in table with some content
     * Example: Then I should see "Priority" in "Warehouse" table
     * Example: Then I should not see "Apple" in "Basket" table
     *
     * @Then /^I should (?P<type>(see|not see)) "(?P<content>([\w\s]+))" in "(?P<tableContent>([\w\s]+))" table$/
     */
    public function assertContentInTable($type, $content, $tableContent)
    {
        /** @var Table $table */
        $table = $this->findElementContains('Table', $tableContent);
        $result = $table->has('named', ['content', $content]);
        if ($type === 'see') {
            self::assertTrue($result, "There is no text '$content' in table '$tableContent'");
        } else {
            self::assertFalse($result, "There is a text '$content' in table '$tableContent'");
        }
    }

    /**
     * String filter
     * Example: When I filter First Name as Contains "Aadi"
     * Example: And filter Name as is equal to "User"
     *
     * @When /^(?:|I )filter (?P<filterName>([\w\s]+)) as (?P<type>([\w\s]+)) "(?P<value>([\w\s\.\_\%]+))"$/
     */
    public function applyStringFilter($filterName, $type, $value)
    {
        /** @var GridFilterStringItem $filterItem */
        $filterItem = $this->getGridFilters()->getFilterItem('GridFilterStringItem', $filterName);

        $filterItem->open();
        $filterItem->selectType($type);
        $filterItem->setFilterValue($value);
        $filterItem->submit();
    }

    //@codingStandardsIgnoreStart
    /**
     * Filter grid by to dates between or not between
     * Date must be valid format for DateTime php class e.g. 2015-12-24, 2015-12-26 8:30:00, 30 Jun 2015
     * Example: When I filter Date Range as between "2015-12-24" and "2015-12-26"
     * Example: But when I filter Created At as not between "25 Jun 2015" and "30 Jun 2015"
     *
     * @When /^(?:|when )(?:|I )filter (?P<filterName>([\w\s]+)) as (?P<type>(between|not between)) "(?P<start>.+)" and "(?P<end>.+)"$/
     */
    //@codingStandardsIgnoreEnd
    public function appllyDateTimeFilter($filterName, $type, $start, $end)
    {
        /** @var GridFilterDateTimeItem $filterItem */
        $filterItem = $this->getGridFilters()->getFilterItem('GridFilterDateTimeItem', $filterName);

        $filterItem->open();
        $filterItem->selectType($type);
        $filterItem->setStartTime(new \DateTime($start));
        $filterItem->setEndTime(new \DateTime($end));
        $filterItem->submit();
    }

    /**
     * Check checkboxes in multiple select filter
     * Example: When I check "Task, Email" in Activity Type filter
     *
     * @When /^(?:|I )check "(?P<filterItems>.+)" in (?P<filterName>([\w\s]+)) filter$/
     */
    public function iCheckCheckboxesInFilter($filterName, $filterItems)
    {
        /** @var MultipleChoice $filterItem */
        $filterItem = $this->getGridFilters()->getFilterItem('MultipleChoice', $filterName);
        $filterItems = array_map('trim', explode(',', $filterItems));

        $filterItem->checkItems($filterItems);
    }

    /**
     * Reset filter
     * Example: And I reset Activity Type filter
     *
     * @When /^(?:|I )reset (?P<filterName>([\w\s]+)) filter$/
     */
    public function resetFilter($filterName)
    {
        $filterItem = $this->getGridFilters()->getFilterItem('GridFilterDateTimeItem', $filterName);
        $filterItem->reset();
    }

    /**
     * @When /^(?:|I )reset "(?P<filterName>([\w\s\:]+))" filter on grid "(?P<grid>([\w\s]+))"$/
     *
     * @param string $filterName
     * @param string $grid
     */
    public function resetFilterOfGrid($filterName, $grid)
    {
        $grid = $grid ?: 'Grid';

        $filterItem = $this->getGridFilters($grid)->getFilterItem($grid . 'FilterItem', $filterName);
        $filterItem->reset();
    }

    /**
     * @When /^(?:|I )check All Visible records in grid$/
     */
    public function iCheckAllVisibleRecordsInGrid()
    {
        $this->getGrid()->massCheck('All visible');
    }

    /**
     * @When /^(?:|I )check all records in grid$/
     */
    public function iCheckAllRecordsInGrid()
    {
        $this->getGrid()->massCheck('All');
    }

    /**
     * Asserts that no record with provided content in grid
     * Example: And there is no "Glorious workflow" in grid
     *
     * @Then /^there is no "(?P<record>([\w\s\%]+))" in grid$/
     * @param string $record
     */
    public function thereIsNoInGrid($record)
    {
        $gridRow = $this->findElementContains('GridRow', $record);
        self::assertFalse($gridRow->isIsset(), sprintf('Grid still has record with "%s" content', $record));
    }

    /**
     * @Then there is no records in grid
     * @Then all records should be deleted
     */
    public function thereIsNoRecordsInGrid()
    {
        self::assertCount(0, $this->getGrid()->getRows());
    }

    /**
     * @Then /^there is no records in grid "(?P<grid>([\w\s]+))"$/
     *
     * @param string $grid
     */
    public function thereIsNoRecordsInGridWithName($grid)
    {
        self::assertCount(0, $this->getGrid($grid)->getRows());
    }

    /**
     * Click on row action. Row will founded by it's content
     * Example: And click view Charlie in grid
     * Example: When I click edit Call to Jennyfer in grid
     * Example: And I click delete Sign a contract with Charlie in grid
     *
     * @Given /^(?:|I )click (?P<action>(Clone|(?!\bon)\w)*) (?P<content>(?:[^"]|\\")*) in grid$/
     */
    public function clickActionInRow($content, $action)
    {
        $this->getGrid()->clickActionLink($content, $action);
    }

    /**
     * @Given /^(?:|I )click (?P<action>[\w\s]*) on (?P<content>(?:[^"]|\\")*) in grid "(?P<grid>([\w\s]+))"$/
     *
     * @param string $content
     * @param string $action
     * @param string $grid
     */
    public function clickActionInRowOfGrid($content, $action, $grid)
    {
        $this->getGrid($grid)->clickActionLink($content, $action);
    }

    /**
     * Click on row in grid
     * Example: When click on Charlie in grid
     *
     * @Given /^(?:|I )click on (?P<content>(?:[^"]|\\")*) in grid$/
     */
    public function clickOnRow($content)
    {
        $this->getGrid()->getRowByContent($content)->click();
        // Keep this check for sure that ajax is finish
        $this->getSession()->getDriver()->waitForAjax();
    }

    /**
     * Expand grid view options.
     * Example: I click Options in grid view
     *
     * @Given I click Options in grid view
     */
    public function clickViewOptions()
    {
        $this->elementFactory->createElement('GridViewOptionsLink')->click();
    }

    /**
     * Click on item in grid view options.
     * Example: Given I click on "Some item" in grid view options
     * @param string $title
     *
     * @Given I click on :title in grid view options
     */
    public function clickLinkInViewOptions($title)
    {
        $this->elementFactory->createElement('GridViewOptions')->clickLink($title);
    }

    /**
     * Check that item in grid view options exists.
     * Example: Then I should see "Some item" in grid view options
     * @param string $title
     *
     * @Then I should see :title in grid view options
     */
    public function iShouldSeeItemInViewOptions($title)
    {
        self::assertNotNull($this->elementFactory->createElement('GridViewOptions')->findLink($title));
    }

    /**
     * Check that item in grid view options does not exist.
     * Example: Then I should not see "Some item" in grid view options
     * @param string $title
     *
     * @Then I should not see :title in grid view options
     */
    public function iShouldNotSeeItemInViewOptions($title)
    {
        self::assertNull($this->elementFactory->createElement('GridViewOptions')->findLink($title));
    }

    /**
     * @When /^(?:|I )confirm deletion$/
     */
    public function confirmDeletion()
    {
        $this->elementFactory->createElement('Modal')->clickLink('Yes, Delete');
    }

    /**
     * @When cancel deletion
     */
    public function cancelDeletion()
    {
        $this->elementFactory->createElement('Modal')->clickLink('Cancel');
    }

    /**
     * @Then /^(?:|I )should see success message with number of records were deleted$/
     */
    public function iShouldSeeSuccessMessageWithNumberOfRecordsWereDeleted()
    {
        $flashMessage = $this->getSession()->getPage()->find('css', '.flash-messages-holder');

        self::assertNotNull($flashMessage, 'Can\'t find flash message');

        $regex = '/\d+ entities were deleted/';
        self::assertRegExp($regex, $flashMessage->getText());
    }

    /**
     * Check that mass action link is not available in grid mass actions
     * Example: Then I shouldn't see Delete action
     *
     * @Then /^(?:|I )shouldn't see (?P<action>(?:[^"]|\\")*) action$/
     */
    public function iShouldNotSeeDeleteAction($action)
    {
        $grid = $this->getGrid();
        self::assertNull(
            $grid->getMassActionLink($action),
            sprintf('%s mass action should not be accassable', $action)
        );
    }

    /**
     * Check that record with provided name exists in grid
     * Example: Then I should see First test group in grid
     *
     * @Then /^(?:|I )should see (?P<recordName>(?:[^"]|\\")*) in grid$/
     */
    public function iShouldSeeRecordInGrid($recordName)
    {
        $this->getGrid()->getRowByContent($recordName);
    }

    /**
     * Check that given collection of records exists in grid
     * Example: Then I should see following records in grid:
     *            | Alice1  |
     *            | Alice10 |
     * @Then /^(?:|I )should see following records in grid:$/
     */
    public function iShouldSeeFollowingRecordsInGrid(TableNode $table)
    {
        foreach ($table->getRows() as list($value)) {
            $this->iShouldSeeRecordInGrid($value);
        }
    }

    /**
     * @param string $stringNumber
     * @return int
     */
    private function getNumberFromString($stringNumber)
    {
        switch (trim($stringNumber)) {
            case 'first':
                return 1;
            case 'second':
                return 2;
            default:
                return (int) $stringNumber;
        }
    }

    /**
     * @param string|null $grid
     * @return Grid
     */
    private function getGrid($grid = null)
    {
        $grid = $grid ?: 'Grid';

        return $this->elementFactory->createElement($grid);
    }

    /**
     * @return GridPaginator
     */
    private function getGridPaginator()
    {
        return $this->elementFactory->createElement('GridPaginator');
    }

    /**
     * @param string|null $grid
     * @return GridFilters
     */
    private function getGridFilters($grid = null)
    {
        $grid = $grid ?: 'Grid';

        $filters = $this->elementFactory->createElement($grid . 'Filters');
        if (!$filters->isVisible()) {
            $gridToolbarActions = $this->elementFactory->createElement($grid . 'ToolbarActions');
            if ($gridToolbarActions->isVisible()) {
                $gridToolbarActions->getActionByTitle('Filters')->click();
            } else {
                $filterStateElementName = $grid . 'FiltersState';
                $filterState = $this->elementFactory->createElement($filterStateElementName);
                self::assertNotNull($filterState);

                $filterState->click();
            }
        }

        return $filters;
    }
}
