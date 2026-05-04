import assert from "node:assert/strict";
import test from "node:test";

import {
    cloneAnswerMap,
    hasSavedAnswer,
    isAnswerDirty,
} from "./answer-state.js";

test("draft answers do not mutate saved answers", () => {
    const question = { id: "2", type: "mcq" };
    const savedAnswers = cloneAnswerMap({
        2: { selected_option_id: 10, text_answer: null },
    });
    const draftAnswers = cloneAnswerMap(savedAnswers);

    draftAnswers[2].selected_option_id = 11;

    assert.equal(savedAnswers[2].selected_option_id, 10);
    assert.equal(isAnswerDirty(question, draftAnswers, savedAnswers), true);
});

test("blank saved text is not treated as answered", () => {
    const question = { id: "7", type: "open_text" };

    assert.equal(
        hasSavedAnswer(question, {
            7: { selected_option_id: null, text_answer: "" },
        }),
        false,
    );
});

test("clearing a saved text answer remains dirty so it can autosave", () => {
    const question = { id: "7", type: "open_text" };

    assert.equal(
        isAnswerDirty(
            question,
            { 7: { text_answer: "" } },
            { 7: { text_answer: "Keep this concise." } },
        ),
        true,
    );
});
