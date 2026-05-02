from pathlib import Path

from fpdf import FPDF


LONG_BOND_MM = (215.9, 330.2)  # 8.5 x 13 in
SYSTEM_NAME = "Web-Based Facial Recognition Attendance and Daily Time Record System for NORSU OJT Students"


class QuestionnairePDF(FPDF):
    def footer(self):
        self.set_y(-10)
        self.set_font("Helvetica", "", 8)
        self.set_text_color(100, 100, 100)
        self.cell(0, 5, f"-- {self.page_no()} --", align="C")
        self.set_text_color(0, 0, 0)


def ensure_space(pdf: QuestionnairePDF, needed_h: float):
    if pdf.get_y() + needed_h > (pdf.h - pdf.b_margin):
        pdf.add_page()


def paragraph(pdf: QuestionnairePDF, text: str, size: float = 10.2, line_h: float = 5):
    pdf.set_font("Helvetica", "", size)
    pdf.multi_cell(0, line_h, text, new_x="LMARGIN", new_y="NEXT")


def heading(pdf: QuestionnairePDF, text: str, size: float = 11):
    pdf.set_font("Helvetica", "B", size)
    pdf.multi_cell(0, 6, text, new_x="LMARGIN", new_y="NEXT")


def line_field(pdf: QuestionnairePDF, label: str):
    ensure_space(pdf, 8)
    pdf.set_font("Helvetica", "", 10.2)
    pdf.cell(pdf.get_string_width(label) + 1, 6, label)
    x1 = pdf.get_x()
    y = pdf.get_y() + 5
    x2 = pdf.w - pdf.r_margin
    pdf.line(x1, y, x2, y)
    pdf.ln(6.5)


def scale_header(pdf: QuestionnairePDF, text: str):
    pdf.set_font("Helvetica", "", 10)
    pdf.multi_cell(0, 5, text)
    pdf.set_font("Helvetica", "B", 9.6)
    pdf.cell(138, 6, "Questions")
    x = pdf.get_x()
    for v in ["1", "2", "3", "4"]:
        pdf.cell(10, 6, v, align="C")
    pdf.ln(6)
    pdf.line(pdf.l_margin, pdf.get_y(), pdf.w - pdf.r_margin, pdf.get_y())
    pdf.ln(1.5)


def question_row(pdf: QuestionnairePDF, text: str):
    ensure_space(pdf, 12)
    y0 = pdf.get_y()
    pdf.set_font("Helvetica", "", 9.8)
    pdf.multi_cell(138, 4.7, text, new_x="RIGHT", new_y="TOP")
    x = pdf.get_x()
    y = y0 + 0.6
    box = 5.6
    gap = 4.4
    for _ in range(4):
        pdf.rect(x, y, box, box)
        x += box + gap
    pdf.set_y(max(pdf.get_y(), y0 + 8.2))
    pdf.ln(0.4)


def feedback_item(pdf: QuestionnairePDF, prompt: str, lines: int = 2):
    ensure_space(pdf, 8 + lines * 6)
    paragraph(pdf, prompt, size=10, line_h=4.8)
    for _ in range(lines):
        y = pdf.get_y() + 5.2
        pdf.line(pdf.l_margin, y, pdf.w - pdf.r_margin, y)
        pdf.ln(5.8)


def common_letter_intro(pdf: QuestionnairePDF):
    paragraph(
        pdf,
        "Dear Respondent,\n"
        f"Thank you very much for using {SYSTEM_NAME}. This project is designed to automate attendance and "
        "daily time record processes with facial verification, audit tracking, and role-based management for "
        "students, coordinators, and administrators.\n"
        "In this regard, we would like to seek your cooperation by accomplishing this survey form to help us "
        "evaluate the quality and assess the level of acceptability of the application to you as our target user. "
        "Your feedback will be used to further improve its features to serve you better.\n"
        "Rest assured that the data gathered through this survey will be treated with utmost confidentiality in "
        "accordance with the provisions of RA 10173 or the Data Privacy Act of 2012.\n"
        "Again, thank you very much for being our partner in this endeavor.",
        size=10.2,
        line_h=4.9,
    )
    pdf.ln(1.4)


def questionnaire_end_user(pdf: QuestionnairePDF):
    pdf.add_page()
    common_letter_intro(pdf)
    heading(pdf, "Questionnaire 1: End User (Students)")
    heading(pdf, "PART 1: RESPONDENT'S PROFILE", size=10.8)
    line_field(pdf, "Name (Optional):")
    line_field(pdf, "Age:")
    line_field(pdf, "Role (Student):")
    line_field(pdf, "Affiliated Institution (e.g., NORSU):")

    heading(pdf, "PART 2: SOFTWARE QUALITY EVALUATION", size=10.8)
    paragraph(
        pdf,
        f"Directions: Please evaluate the software quality of {SYSTEM_NAME} based on ISO 25010 Software Product "
        "Quality Standards for end users. Choose the appropriate box using the four-point scale (4 as the highest, 1 as the lowest).",
        size=9.8,
        line_h=4.7,
    )

    ensure_space(pdf, 40)
    paragraph(
        pdf,
        "1. Functional Suitability: Represents the degree to which a product or system provides functions that "
        "meet stated and implied needs when used under specified conditions.",
        size=9.8,
        line_h=4.7,
    )
    scale_header(pdf, "1 - Poorly Functional    2 - Moderately Functional    3 - Functional    4 - Very Functional")
    question_row(pdf, "Functional completeness: Degree to which the Web-Based Facial Recognition Attendance and Daily Time Record System covers required OJT tasks (e.g., facial identity verification, student time-in/time-out, lunch break logging, and daily attendance record tracking).")
    question_row(pdf, "Functional correctness: Degree to which the system provides correct and precise results (e.g., proper match/mismatch decisions and DTR computations).")
    question_row(pdf, "Functional appropriateness: Degree to which the functions facilitate OJT attendance objectives.")

    ensure_space(pdf, 40)
    paragraph(
        pdf,
        "2. Performance Efficiency: Represents performance relative to the number of resources used under stated conditions.",
        size=9.8,
        line_h=4.7,
    )
    scale_header(pdf, "1 - Poorly Efficient    2 - Moderately Efficient    3 - Efficient    4 - Very Efficient")
    question_row(pdf, "Time behavior: Degree to which response times meet requirements (e.g., face verification speed and attendance submission time).")
    question_row(pdf, "Resource utilization: Degree to which the system operates efficiently on your device.")
    question_row(pdf, "Capacity: Degree to which the system handles expected users without performance issues.")

    ensure_space(pdf, 62)
    paragraph(
        pdf,
        "3. Usability: Degree to which a product or system can be used by specified users to achieve specified goals "
        "with effectiveness, efficiency, and satisfaction.",
        size=9.8,
        line_h=4.7,
    )
    scale_header(pdf, "1 - Poorly Usable    2 - Moderately Usable    3 - Usable    4 - Very Usable")
    question_row(pdf, "Appropriateness recognizability: Degree to which users quickly recognize the platform as suitable for attendance recording needs.")
    question_row(pdf, "Learnability: Degree to which the student interface is easy to learn and navigate.")
    question_row(pdf, "Operability: Degree to which the platform is easy to control during attendance transactions.")
    question_row(pdf, "User error protection: Degree to which the system protects against mistakes (e.g., duplicate record attempts).")
    question_row(pdf, "User interface aesthetics: Degree to which the interface enables a pleasing, organized, and visually satisfying interaction.")
    question_row(pdf, "Accessibility: Degree to which the system is usable in local contexts, including low-bandwidth environments.")

    heading(pdf, "PART 3: FEEDBACK AND SUGGESTIONS", size=10.8)
    feedback_item(pdf, "1. Please provide further comments about your experience using the system.", 2)
    feedback_item(pdf, "2. Kindly identify its strong points to be enhanced and its weak points that need to be addressed for improvements.", 2)
    feedback_item(pdf, "3. Do you have any thoughts on other features/functions that can be added?", 2)
    feedback_item(pdf, "4. Kindly share any suggestions with us so we can improve it.", 2)
    line_field(pdf, "Respondent's Signature:")
    line_field(pdf, "Date of Evaluation:")


def questionnaire_it_expert(pdf: QuestionnairePDF):
    pdf.add_page()
    heading(pdf, "Questionnaire 2: IT Expert")
    heading(pdf, "PART 1: RESPONDENT'S PROFILE", size=10.8)
    line_field(pdf, "Name (Optional):")
    line_field(pdf, "Age:")
    line_field(pdf, "Specialization/Expertise:")
    line_field(pdf, "Affiliated Institution/Company:")

    heading(pdf, "PART 2: SOFTWARE QUALITY EVALUATION", size=10.8)
    paragraph(
        pdf,
        f"Directions: Please evaluate the software quality of {SYSTEM_NAME} based on the eight main characteristics of ISO 25010 "
        "Software Product Quality Standards. Choose the appropriate box using the four-point scale (4 as the highest, 1 as the lowest).",
        size=9.8,
        line_h=4.7,
    )

    expert_sections = [
        ("1. Functional Suitability", "1 - Poorly Functional    2 - Moderately Functional    3 - Functional    4 - Very Functional", [
            "Functional completeness: Degree to which the system fulfills key attendance and DTR workflow requirements.",
            "Functional correctness: Degree to which core logic executes accurately without data loss or inconsistency.",
            "Functional appropriateness: Degree to which architecture and feature set support actual institutional operations.",
        ]),
        ("2. Performance Efficiency", "1 - Poorly Efficient    2 - Moderately Efficient    3 - Efficient    4 - Very Efficient", [
            "Time behavior: Processing time for authentication, face verification, and attendance submission.",
            "Resource utilization: Efficient use of server and client resources under regular traffic.",
            "Capacity: Ability to maintain acceptable performance with concurrent users.",
        ]),
        ("3. Compatibility", "1 - Poorly Compatible    2 - Moderately Compatible    3 - Compatible    4 - Very Compatible", [
            "Co-existence: Degree to which the system performs efficiently alongside other applications/services.",
            "Interoperability: Effectiveness of data exchange among frontend, backend, and integrated services.",
        ]),
        ("4. Usability", "1 - Poorly Usable    2 - Moderately Usable    3 - Usable    4 - Very Usable", [
            "Appropriateness recognizability: Clarity of admin/coordinator controls for intended operations.",
            "Learnability: Intuitiveness of workflows for technical and non-technical users.",
            "Operability: Ease of managing users, attendance records, and system settings.",
            "User error protection: Safeguards against destructive or invalid operations.",
            "User interface aesthetics: Quality and organization of visual design and layout.",
            "Accessibility: Responsiveness and usability across available devices and contexts.",
        ]),
        ("5. Reliability", "1 - Poorly Reliable    2 - Moderately Reliable    3 - Reliable    4 - Very Reliable", [
            "Maturity: System stability under normal operation.",
            "Availability: Operational readiness and uptime of core services.",
            "Fault tolerance: Ability to handle failures gracefully without severe disruption.",
            "Recoverability: Efficiency in restoring service and preserving data integrity.",
        ]),
        ("6. Security", "1 - Poorly Secure    2 - Moderately Secure    3 - Secure    4 - Very Secure", [
            "Confidentiality: Data access is limited to authorized users.",
            "Integrity: Protection against unauthorized data modification.",
            "Non-repudiation: Important events are traceable via logs.",
            "Accountability: Critical actions are attributable to specific accounts.",
            "Authenticity: Identity verification controls are sufficient.",
        ]),
        ("7. Maintainability", "1 - Poorly Maintainable    2 - Moderately Maintainable    3 - Maintainable    4 - Very Maintainable", [
            "Modularity: Components are structured to minimize change impact.",
            "Reusability: Parts can be reused across features/modules.",
            "Analyzability: Failures and defects are easy to diagnose.",
            "Modifiability: Changes can be made without degrading core functions.",
            "Testability: Core functions are practical to test and validate.",
        ]),
        ("8. Portability", "1 - Poorly Portable    2 - Moderately Portable    3 - Portable    4 - Very Portable", [
            "Adaptability: System can function across varied environments.",
            "Installability: Ease of deployment and setup.",
            "Replaceability: Ability to replace fragmented legacy processes in one platform.",
        ]),
    ]

    for title, scale, rows in expert_sections:
        ensure_space(pdf, 26)
        paragraph(pdf, title, size=9.9, line_h=4.8)
        scale_header(pdf, scale)
        for r in rows:
            question_row(pdf, r)

    heading(pdf, "PART 3: FEEDBACK AND SUGGESTIONS", size=10.8)
    feedback_item(pdf, "1. Please provide further technical comments regarding the architecture and logic of the system.", 2)
    feedback_item(pdf, "2. Kindly identify strong points and weak points that need addressing for improvements.", 2)
    feedback_item(pdf, "3. Do you have thoughts on additional backend/security features that should be added?", 2)
    feedback_item(pdf, "4. Kindly share suggestions to optimize the system for scalability and local bandwidth constraints.", 2)
    line_field(pdf, "Respondent's Signature:")
    line_field(pdf, "Date of Evaluation:")


def main():
    out_path = Path(r"c:\xampp\OJT\norsu-ojt-dtr\NORSU_DTR_Questionnaire_LongBond_v3.pdf")
    pdf = QuestionnairePDF(orientation="P", unit="mm", format=LONG_BOND_MM)
    pdf.set_margins(18, 16, 18)
    pdf.set_auto_page_break(auto=True, margin=16)
    pdf.set_title("NORSU DTR Questionnaire")
    pdf.set_author("NORSU OJT DTR")

    questionnaire_end_user(pdf)
    questionnaire_it_expert(pdf)

    pdf.output(str(out_path))
    print(out_path)


if __name__ == "__main__":
    main()
