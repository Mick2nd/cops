<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

class PageAllBooks extends Page
{
    public function InitializeContent ()
    {
        $this->title = localize ("allbooks.title");
        if (getCurrentOption ("titles_split_first_letter") == 1) {
            $this->entryArray = BookServices::getAllBooksS();
        }
        else {
            list ($this->entryArray, $this->totalNumber) = BookServices::getBooksS ($this->n);
        }
        $this->idPage = Book::ALL_BOOKS_ID;
    }
}
