<?php
/**
 * Simple AJAX Contact Form for SwiffyBlog
 */

return [
    'name' => 'Swiffy Contact Form',
    'description' => 'A simple contact form shortcode [contact].',
    'author' => 'Swiffy Blog People',
    'version' => '1.0.0',
    'hooks' => [
        'render_content' => function($content) {
            if (strpos($content, '[contact]') !== false) {
                $form = '
                <div id="swiffy-contact-form" style="max-width: 500px; margin: 20px 0; background: rgba(128,128,128,0.05); padding: 25px; border-radius: 8px; border: 1px solid rgba(128,128,128,0.1);">
                    <h3 style="margin-top: 0;">Contact Us</h3>
                    <div id="contact-status" style="margin-bottom: 15px; display: none; padding: 10px; border-radius: 4px;"></div>
                    <form onsubmit="sendswiffyContact(event)">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Name</label>
                            <input type="text" id="c-name" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd; background: #fff; color: #333;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email</label>
                            <input type="email" id="c-email" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd; background: #fff; color: #333;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Message</label>
                            <textarea id="c-msg" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd; min-height: 100px; background: #fff; color: #333;"></textarea>
                        </div>
                        <button type="submit" style="background: #2271b1; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Send Message</button>
                    </form>
                </div>
                <script>
                function sendswiffyContact(e) {
                    e.preventDefault();
                    const status = document.getElementById("contact-status");
                    status.style.display = "block";
                    status.style.background = "#fff3cd";
                    status.innerText = "Sending...";
                    
                    // In a real app, this would send an AJAX request to a PHP handler
                    setTimeout(() => {
                        status.style.background = "#d4edda";
                        status.style.color = "#155724";
                        status.innerText = "Thank you! Your message has been sent (Demo only).";
                        e.target.reset();
                    }, 1000);
                }
                </script>';
                $content = str_replace('[contact]', $form, $content);
            }
            return $content;
        }
    ]
];
