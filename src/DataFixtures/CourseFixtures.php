<?php

namespace App\DataFixtures;

use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $courses = [
            // Арендные
            [
                'code' => 'AREND199230SKLADS',
                'title' => 'Портфель роста 2021',
                'courseType' => 1,
                'price' => 2021,
            ],
            [
                'code' => 'AREND948120385129',
                'title' => 'Успешная торговля каждый день',
                'courseType' => 1,
                'price' => 1000,
            ],
            [
                'code' => 'AREND318305889120',
                'title' => 'Покупай/продовай на сигналах. Ленивый трейдинг',
                'courseType' => 1,
                'price' => 3000,
            ],
            // Бесплатные курсы
            [
                'code' => 'BPSKSODSAJGJSKAOD983A',
                'title' => 'C чего начать новичку?',
                'courseType' => 2,
                'price' => 0,
            ],
            [
                'code' => 'JZLAO2390KSALLFASK123',
                'title' => 'Как выбрать надежного брокера?',
                'courseType' => 2,
                'price' => 0
            ],
            // Покупные
            [
                'code' => 'MLSADKLD13213KSDMDNVM35',
                'title' => 'Основы рынка',
                'courseType' => 3,
                'price' => 15000,
            ],
            [
                'code' => 'QNDIQJWDALSDASDJGLSAD',
                'title' => 'Инвестор',
                'courseType' => 3,
                'price' => 50000,
            ],
            [
                'code' => 'MSALDLGSALDFJASLDDASODP',
                'title' => 'Трейдер',
                'courseType' => 3,
                'price' => 65000,
            ],
        ];

        foreach ($courses as $course) {
            $newCourse = new Course();
            $newCourse->setCode($course['code']);
            $newCourse->setTitle($course['title']);
            $newCourse->setCourseType($course['courseType']);
            if (isset($course['price'])) {
                $newCourse->setPrice($course['price']);
            }
            $manager->persist($newCourse);
        }
        $manager->flush();
    }
}
