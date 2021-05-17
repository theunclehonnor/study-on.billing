<?php


namespace App\Command;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Twig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PaymentReportCommand extends Command
{
    private $twig;
    private $mailer;
    private $manager;

    protected static $defaultName = 'payment:report';
    public function __construct(Twig $twig, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'email_address',
                null,
                'Адрес пользователя',
                'user@yandex.ru'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Создаем пользователя по которому будем
        /** @var User $user */
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $input->getArgument('email_address'),
        ]);

        // Транзакции за последний месяц с текущей даты
        $transactions = $this->manager->getRepository(Transaction::class)->findPaidCoursesPerMonth($user);

        if ($transactions !== []) {
            // Период создания отчета
            // Текущая дата
            $endDate = (new \DateTime())->format('Y-m-d');
            // Месяц назад
            $startDate = (new \DateTime())->modify('-1 month')->format('Y-m-d');

            // Найдем итоговую сумму за данный период
            $total = 0;
            foreach ($transactions as $transaction) {
                $total += $transaction['sum'];
            }

            // Шаблон сообщения
            $html = $this->twig->render(
                'mailer/ monthlyTotalAmountReport.html.twig',
                [
                    'transactions' => $transactions,
                    'total' => $total,
                    'endDate' => $endDate,
                    'startDate' => $startDate,
                ]
            );

            // Формируем сообщение пользователю от администратора
            $message = (new Email())
                ->to($input->getArgument('email_address'))
                ->from('admin@yandex.ru')
                ->subject('Отчет по данным об оплаченных курсах за месяц')
                ->html($html);

            try {
                // Отправка сообщения пользователю
                $this->mailer->send($message);
            } catch (TransportExceptionInterface $e) {
                $output->writeln($e->getMessage());

                $output->writeln('Возникла ошибка. Не удалось отправить сообщение');
                return Command::FAILURE;
            }
        }

        $output->writeln('Отчет успешно сформирован');
        return Command::SUCCESS;
    }
}
