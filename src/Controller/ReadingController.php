<?php

namespace AndyTruong\Bible\Controller;

use AndyTruong\Bible\Application;
use AndyTruong\Bible\Entity\TranslationEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class ReadingController
{

    /** @var Application */
    private $app;

    /** @var EntityManagerInterface Entity manager. */
    private $em;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->em = $app->getEntityManager();
    }

    /**
     * @url GET /
     * @view bible/index
     */
    public function getHome()
    {
        return [];
    }

    /**
     * @url GET /admin
     * @view bible/index
     */
    protected function getAdminHome()
    {
        return ['isAdmin' => true];
    }

    /**
     * @url GET /bible/versions
     */
    public function getVersionsAction()
    {
        return array_map(function(TranslationEntity $translation) {
            return [
                'id'       => $translation->getId(),
                'name'     => $translation->getName(),
                'writing'  => $translation->getWriting(),
                'language' => [
                    'id'        => $translation->getLanguage()->getId(),
                    'direction' => $translation->getLanguage()->getDirection(),
                ],
            ];
        }, $this->em
                ->getRepository('AndyTruong\Bible\Entity\TranslationEntity')
                ->findAll())
        ;
    }

    /**
     * @url GET /bible/books
     */
    public function getBooksAction()
    {
        return $this->app->configGet(null, 'books');
    }

    /**
     * @url GET /bible/{translation}/{book}/{chapter_number}
     * @param string $translation
     * @param string $book
     * @param int $chapter_number
     */
    public function getChapterAction($translation, $book, $chapter_number)
    {
        if (is_numeric($book)) {
            $book_number = $book;
        }
        else {
            foreach ($this->getBooksAction() as $i => $book_info) {
                if ($book === $book_info[0]) {
                    $book_number = $i + 1;
                }
            }
        }

        if (!isset($book_number)) {
            return [];
        }

        $query = $this->em->createQuery(
            "SELECT v.book, v.chapter, v.number, v.body"
            . " FROM AndyTruong\Bible\Entity\VerseEntity v"
            . "     JOIN v.translation t"
            . " WHERE t.name = :translation AND v.book = :book AND v.chapter = :chapter"
            . " ORDER BY v.number"
        );

        $query->setParameter(':translation', $translation);
        $query->setParameter(':book', $book_number);
        $query->setParameter(':chapter', $chapter_number);

        return $query->getResult();
    }

    /**
     * Full text search.
     *
     * @url GET /bible/find/{translation}/{keywords}
     * @param string $translation
     * @param string $keywords
     */
    public function findAction($translation, $keywords)
    {
        /* @var $query Query  */
        $query = $this->em->createQuery(
            "SELECT v.book, v.chapter, v.number, v.body"
            . " FROM AndyTruong\Bible\Entity\VerseEntity v"
            . "     JOIN v.translation t"
            . " WHERE t.name = :translation AND v.body LIKE :keywords"
            . " ORDER BY v.book, v.chapter, v.number"
        );
        $query->setParameter(':translation', $translation);
        $query->setParameter(':keywords', "%{$keywords}%");

        return $query->execute();
    }

}
