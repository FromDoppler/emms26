<?php
$faqItems = [
  [
    "question" => "🕵️‍♀️ ¿Por qué asistir al EMMS?",
    "answer" => "Es el evento online y gratuito de Marketing Digital más importante de España y Latinoamérica. Cada año nos eligen expertos de compañías líderes de la industria para dar a conocer las principales tendencias en su sector."
  ],
  [
    "question" => "🎁 ¿Qué obtengo al registrarme al evento?",
    "answer" => "Con tu registro podrás acceder a todas las Conferencias de esta y todas las ediciones anteriores para siempre. Además, podrás acceder a una biblioteca repleta de recursos como E-books, Plantillas, descuentos y material audiovisual para que puedas hacer crecer tu negocio aún más."
  ],
  [
    "question" => "📅 ¿Cuándo se realizará el EMMS 2026?",
    "answer" => "El EMMS 2026 se realizará el 14, 15 y 16 de julio. Será una edición especial por los 20 años de Doppler que volverá a convocar a los mejores speakers de la historia del evento. Registrándote al EMMS recibirás por Email todos las novedades."
  ],
  [
    "question" => "📍 ¿Dónde serán los eventos?",
    "answer" => "El EMMS es un evento online. Es decir, podrás verlo desde cualquier dispositivo, estés donde estés e, incluso, volver a ver las ediciones anteriores. Se transmite desde este mismo Sitio Web. Cuando se acerque la fecha irás recibiendo recordatorios e instrucciones."
  ],
  [
    "question" => "💵 ¿Tengo que pagar inscripción?",
    "answer" => "El EMMS tiene un registro totalmente gratuito, válido para acceder a las Conferencias y a la Biblioteca de Recursos. Si, además, quieres capacitarte con Workshops prácticos, a los que puedes acceder de por vida, pronto podrás comprar tu entrada VIP."
  ],
  [
    "question" => "💻 ¿Cómo accedo a la transmisión del EMMS si ya me registré?",
    "answer" => "Podrás seguir la transmisión del EMMS directamente desde este mismo Sitio Web en las fechas del evento."
  ],
  [
    "question" => "🎥 ¿Están disponibles las grabaciones después del evento?",
    "answer" => "Las Conferencias de las ediciones pasadas están grabadas y puedes acceder a ellas desde <a href='/ediciones-anteriores'>aquí</a>."
  ],
  [
    "question" => "🤔 Me apunté al evento y aún no recibí el Email de confirmación, ¿qué hago?",
    "answer" => "Comunícate con el equipo de Atención al Cliente de Doppler enviando un Email a <a href='mailto:soporte@fromdoppler.com'>soporte@fromdoppler.com</a> para ayudarte a resolverlo."
  ],
  [
    "question" => "📣 Me interesa ser aliado en el evento, ¿todavía estoy a tiempo de sumarme?",
    "answer" => "¡Sí, claro! Comunícate al Email <a href='mailto:partners@fromdoppler.com'>partners@fromdoppler.com</a> para que podamos contarte cuáles son las oportunidades de participar y cómo podrías sumarte."
  ],
  [
    "question" => "🎙Quiero ser Speaker del EMMS, ¿puedo postularme?",
    "answer" => "¡Por supuesto! Escríbenos a <a href='mailto:partners@fromdoppler.com'>partners@fromdoppler.com</a> para comentarnos por qué deberías ser ponente en EMMS y te responderemos a la brevedad."
  ],
  [
    "question" => "📝 ¿Obtengo un certificado de participación por asistir al evento?",
    "answer" => "¡Sí! Podrás descargar tu certificado de asistencia a cada una de las ediciones del EMMS."
  ]
];

$faqStructuredData = [
  "@context" => "https://schema.org",
  "@type" => "FAQPage",
  "mainEntity" => []
];

foreach ($faqItems as $item) {
  $faqStructuredData['mainEntity'][] = [
    "@type" => "Question",
    "name" => strip_tags($item['question']),
    "acceptedAnswer" => [
      "@type" => "Answer",
      "text" => strip_tags($item['answer'])
    ]
  ];
}

?>


<!-- Frequent Questions -->
<section class="emms__frequentquestions emms__frequentquestions--during" id="preguntas-frecuentes">
  <div class="emms__background-a"></div>
  <div class="emms__container--md">
    <h2 class="emms__fade-in">Preguntas frecuentes</h2>
    <ul class="emms__frequentquestions__list emms__frequentquestions__list--during emms__fade-in">
      <?php foreach ($faqItems as $item): ?>
        <li class="emms__frequentquestions__list__item <?= !empty($item['open']) ? 'open' : 'close' ?>">
          <button class="emms__frequentquestions__list__item__head"><?= $item['question'] ?></button>
          <p class="emms__frequentquestions__list__item__content"><?= $item['answer'] ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<script src="/src/<?= VERSION ?>/js/collapsibles.js"></script>
<script type="application/ld+json">
  <?= json_encode($faqStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
